<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Entity\Signal;
use App\Entity\Social;
use App\Entity\Trade;
use App\Entity\Upgrade;
use App\Entity\User;
use App\Entity\Withdrawal;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard')]
final class DashboardController extends AbstractController
{

    private $emailSender;



    public function __construct(MailerService $emailSender)
    {
        $this->emailSender = $emailSender;
    }


    #[Route('/', name: 'dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user);

        //   $this->emailSender->sendEmail($user->getEmail(), "Welcome OnBoard", "email/welcome.twig", [
        //             "name" => $user->getFullname(),
        //             "useremail" => $user->getEmail(),
        //             "ref" => $user->getUid(),
        //             "date" => $user->getCreatedat()->format('Y-m-d'),
        //         ]);

        if ($request->isMethod('POST')) {
            // Retrieve form data
            $tradingType   = $request->request->get('trading_type');
            $currencyPair  = $request->request->get('currency_pair');
            $lotSize       = $request->request->get('lot_size');
            $entryPrice    = $request->request->get('entry_price');
            $stopLoss      = $request->request->get('stop_loss');
            $takeProfit    = $request->request->get('take_profit');
            $tradingAction = $request->request->get('trading_action');
            
            // Basic validation (you can add more advanced validation if needed)
            if (!$tradingType || !$currencyPair || !$lotSize || !$entryPrice || !$stopLoss || !$takeProfit || !$tradingAction) {
                $this->addFlash('error', 'All trade fields are required.');
                return $this->redirectToRoute('dashboard');
            }
            
            // Create and populate a new Trade entity
            $trade = new Trade();
            $trade->setUser($user);
            $trade->setTradingType($tradingType);
            $trade->setCurrencyPair($currencyPair);
            $trade->setLotSize((float)$lotSize);
            $trade->setEntryPrice((float)$entryPrice);
            $trade->setStopLoss((float)$stopLoss);
            $trade->setTakeProfit((float)$takeProfit);
            $trade->setTradingAction($tradingAction);
            $trade->setStatus('OPEN'); // Initial status
            $trade->setCreatedAt(new \DateTime());
            $trade->setUpdatedAt(new \DateTime());
            
            // Persist and flush the new trade
            $entityManager->persist($trade);
            $entityManager->flush();
            
            $this->addFlash('success', 'Trade executed successfully.');
            return $this->redirectToRoute('dashboard');
        }
        
        // Fetch the logged in user's trade history (sorted by newest first)
        $trades = $entityManager->getRepository(Trade::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        // Render the dashboard template (which includes the TradingView widget code as-is).
       
        return $this->render('dashboard/index.html.twig', [
            'trades' => $trades,
        ]);
    }

    #[Route('/api/close-trade', name: 'close-trade')]
    public function closeTrade(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Retrieve POST parameters
        $closeTrading  = $request->request->get('close_trading');
        $tradingOption = $request->request->get('trading_option');
        $tradingId     = $request->request->get('trading_id');
        $status        = $request->request->get('status');

        // Validate required parameters
        if (!$closeTrading || !$tradingOption || !$tradingId || !$status) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'failed',
                'message' => 'Missing required parameters.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Fetch the trade entity by its ID
        $trade = $entityManager->getRepository(Trade::class)->find($tradingId);
        if (!$trade) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'failed',
                'message' => 'Trade not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // Update the trade status and timestamp
        $trade->setStatus($status);
        $trade->setUpdatedAt(new \DateTime());

        // Persist changes
        $entityManager->flush();

        return new JsonResponse([
            'error'   => false,
            'notice'  => 'changed',
            'message' => 'Trade closed successfully.'
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/verify', name: 'verify')]
    public function verifyAccount(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user);
        
        
        // Process form submission.
        if ($request->isMethod('POST') && $request->request->has('verify_account')) {
            // Get uploaded files.
            $idCardFrontFile = $request->files->get('idcardFront');
            $idCardBackFile  = $request->files->get('idcardBack');
            
            // Set the upload directory. Make sure this parameter is defined in your services.yaml (or parameters.yaml).
            $uploadDir = $this->getParameter('uploads_directory'); // e.g. "uploads/kyc"
            
            // Process the front of the ID card.
            if ($idCardFrontFile) {
                $newFilenameFront = uniqid('front_') . '.' . $idCardFrontFile->guessExtension();
                try {
                    $idCardFrontFile->move($uploadDir, $newFilenameFront);
                    $user->setIdCardFront($newFilenameFront);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload the front side of the ID card.');
                    return $this->redirectToRoute('verify');
                }
            }
            
            // Process the back of the ID card.
            if ($idCardBackFile) {
                $newFilenameBack = uniqid('back_') . '.' . $idCardBackFile->guessExtension();
                try {
                    $idCardBackFile->move($uploadDir, $newFilenameBack);
                    $user->setIdCardBack($newFilenameBack);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload the back side of the ID card.');
                    return $this->redirectToRoute('app_verify_account');
                }
            }
            
            // Update user's verification status. Assume the user entity has a field "verificationStatus".
            $user->setVerificationStatus('PENDING'); // possible statuses: PENDING, VERIFIED, REJECTED
            $user->setUpdatedAt(new \DateTime());
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Your documents have been submitted for verification.');
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the verification form template.
        return $this->render('dashboard/kyc.html.twig');
    }

    #[Route('/socials', name: 'socials')]
    public function linkSocials(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Process form submission.
        if ($request->isMethod('POST') && $request->request->has('link_account')) {
            // Retrieve form fields.
            $social = $request->request->get('social');
            $socialUsername = $request->request->get('username');
            $socialEmail = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // Validate required fields.
            if (!$social || !$socialUsername || !$socialEmail || !$password || !$confirmPassword) {
                $this->addFlash('error', 'All fields are required.');
                return $this->redirectToRoute('socials');
            }
            
            // Check that password and confirm_password match.
            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Password and Confirm Password do not match.');
                return $this->redirectToRoute('socials');
            }
            
            // Create a new Social entity and set its fields.
            $socialAccount = new Social();
            $socialAccount->setUser($user);
            $socialAccount->setSocial($social);
            $socialAccount->setSocialUsername($socialUsername);
            $socialAccount->setSocialEmail($socialEmail);
            $socialAccount->setSocialPassword($password); // For security, consider hashing or using OAuth.
            $socialAccount->setCreatedAt(new \DateTime());
            $socialAccount->setUpdatedAt(new \DateTime());
            
            // Persist and flush.
            $entityManager->persist($socialAccount);
            $entityManager->flush();
            
            $this->addFlash('success', 'Social media account linked successfully.');
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the form.
        return $this->render('dashboard/socials.html.twig');
    }

    #[Route('/deposit', name: 'deposit')]
    public function deposit(Request $request, EntityManagerInterface $entityManager, FlasherInterface $flasher): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user);
        if (!$user) {
            flash()->error('You must be logged in to make a deposit.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($request->isMethod('POST') && $request->request->has('deposit')) {
            // Retrieve form data.
            $depositMethod = $request->request->get('depositmethod');
            $amount        = $request->request->get('amount');
            $narration     = $request->request->get('narration');
            
            // Validate required fields.
            if (!$depositMethod || !$amount || !$narration) {
                flash()->error('Please fill all required fields.');
                return $this->redirectToRoute('deposit');
            }
            
            // Handle the file upload for deposit proof.
            $depositProofFile = $request->files->get('depositProof');
            $proofFilename = null;
            if ($depositProofFile) {
                // Ensure you have defined "uploads_deposit_proofs" in your configuration.
                $uploadDir = $this->getParameter('uploads_deposit_proofs');
                $proofFilename = uniqid('deposit_', true) . '.' . $depositProofFile->guessExtension();
                try {
                    $depositProofFile->move($uploadDir, $proofFilename);
                } catch (\Exception $e) {
                    flash()->error('Failed to upload deposit proof. Please try again.');
                    return $this->redirectToRoute('deposit');
                }
            }
            
            // Create a new Deposit entity.
            $deposit = new Deposit();
            $deposit->setUser($user);
            $deposit->setDepositMethod($depositMethod);
            $deposit->setAmount((float)$amount);
            $deposit->setNarration($narration);
            $deposit->setDepositProof($proofFilename);
            $deposit->setStatus('PENDING');  // Initial status can be PENDING.
            $deposit->setCreatedAt(new \DateTime());
            $deposit->setUpdatedAt(new \DateTime());
            
            // Persist and flush.
            $entityManager->persist($deposit);
            $entityManager->flush();
            $this->emailSender->sendEmail(
                "fortune@fortunesportfolio.com",
                "New Deposit",
                "email/noti.twig",
                [
                    "title" => "Deposit request",
                    "message" => "New Deposit Request from " . $user->getFullname(),
                ]
            );
            // Use php-flasher to show a pop-up notification.
            flash()->success('Deposit request submitted successfully.');
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the deposit form (GET request).
        return $this->render('dashboard/deposit.html.twig');
    }


    #[Route('/insurancedeposit', name: 'insurancedeposit')]
    public function insurancedeposit(Request $request, EntityManagerInterface $entityManager, FlasherInterface $flasher): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        if (!$user) {
            flash()->error('You must be logged in to make a deposit.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($request->isMethod('POST') && $request->request->has('deposit')) {
            // Retrieve form data.
            $depositMethod = $request->request->get('depositmethod');
            $amount        = $request->request->get('amount');
            $narration     = $request->request->get('narration');
            
            // Validate required fields.
            if (!$depositMethod || !$amount || !$narration) {
                flash()->error('Please fill all required fields.');
                return $this->redirectToRoute('deposit');
            }
            
            // Handle the file upload for deposit proof.
            $depositProofFile = $request->files->get('depositProof');
            $proofFilename = null;
            if ($depositProofFile) {
                // Ensure you have defined "uploads_deposit_proofs" in your configuration.
                $uploadDir = $this->getParameter('uploads_deposit_proofs');
                $proofFilename = uniqid('deposit_', true) . '.' . $depositProofFile->guessExtension();
                try {
                    $depositProofFile->move($uploadDir, $proofFilename);
                } catch (\Exception $e) {
                    flash()->error('Failed to upload deposit proof. Please try again.');
                    return $this->redirectToRoute('deposit');
                }
            }
            
            // Create a new Deposit entity.
            $deposit = new Deposit();
            $deposit->setUser($user);
            $deposit->setDepositMethod($depositMethod);
            $deposit->setAmount((float)$amount);
            $deposit->setNarration($narration);
            $deposit->setDepositProof($proofFilename);
            $deposit->setStatus('PENDING');  // Initial status can be PENDING.
            $deposit->setCreatedAt(new \DateTime());
            $deposit->setUpdatedAt(new \DateTime());
            
            // Persist and flush.
            $entityManager->persist($deposit);
            $entityManager->flush();
            $user = $entityManager->getRepository(User::class)->find($user);
            $this->emailSender->sendEmail(
                "fortune@fortunesportfolio.com",
                "New Deposit",
                "email/noti.twig",
                [
                    "title" => "Deposit request",
                    "message" => "New Deposit Request from " . $user->getFullname(),
                ]
            );
            // Use php-flasher to show a pop-up notification.
            flash()->success('Deposit request submitted successfully.');
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the deposit form (GET request).
        return $this->render('dashboard/insurance.html.twig');
    }

    #[Route('/withdrawal', name: 'withdrawal')]
    public function withdrawal(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user);
        if (!$user) {
            flash()->error("You must be logged in to request a withdrawal.");
            return $this->redirectToRoute('app_login');
        }
        
        if ($request->isMethod('POST') && $request->request->has('withdraw')) {
            // Retrieve common fields.
            $withdrawalMethod = $request->request->get('withdrawalmethod');
            $amount           = $request->request->get('amount');
            $fees             = $request->request->get('fees'); // from form; typically read-only (e.g., "0%")
            $narration        = $request->request->get('narration');
            
            // Retrieve additional fields (all are optional, only one group should be non-empty based on method).
            $bitcoinAddress      = $request->request->get('bitcoin_address');
            $ethereumAddress     = $request->request->get('ethereum_address');
            $litecoinAddress     = $request->request->get('litecoin_address');
            $bitcoincashAddress  = $request->request->get('bitcoincash_address');
            $skrillEmail         = $request->request->get('skrill_email');
            $paypalEmail         = $request->request->get('paypal_email');
            $bankName            = $request->request->get('bank_name');
            $accountNumber       = $request->request->get('account_number');
            $country             = $request->request->get('country');
            $swiftCode           = $request->request->get('swift_code');
            
            // Validate required common fields.
            if (!$withdrawalMethod || !$amount || !$narration) {
                flash()->error("Please fill all required fields.");
                return $this->redirectToRoute('withdrawal');
            }
            
            // Check if the user has sufficient balance.
            // Assuming your User entity has a getBalance() method.
            if ((float)$amount > $user->getBalance()) {
                flash()->error("Insufficient balance for withdrawal.");
                return $this->redirectToRoute('withdrawal');
            }
            
            // Create new Withdrawal entity.
            $withdrawal = new Withdrawal();
            $withdrawal->setUser($user);
            $withdrawal->setWithdrawalMethod($withdrawalMethod);
            $withdrawal->setAmount((float)$amount);
            $withdrawal->setFees($fees);
            $withdrawal->setNarration($narration);
            
            // Set wallet address / bank fields.
            $withdrawal->setBitcoinAddress($bitcoinAddress);
            $withdrawal->setEthereumAddress($ethereumAddress);
            $withdrawal->setLitecoinAddress($litecoinAddress);
            $withdrawal->setBitcoincashAddress($bitcoincashAddress);
            $withdrawal->setSkrillEmail($skrillEmail);
            $withdrawal->setPaypalEmail($paypalEmail);
            $withdrawal->setBankName($bankName);
            $withdrawal->setAccountNumber($accountNumber);
            $withdrawal->setCountry($country);
            $withdrawal->setSwiftCode($swiftCode);
            
            // Set initial status and timestamps.
            $withdrawal->setStatus("PENDING");
            $withdrawal->setCreatedAt(new \DateTime());
            $withdrawal->setUpdatedAt(new \DateTime());

            $user->setBalance($user->getBalance() - (float)$amount);
            
            // Persist to the database.
            $entityManager->persist($withdrawal);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailSender->sendEmail(
                "fortune@fortunesportfolio.com",
                "New Withdrawal",
                "email/noti.twig",
                [
                    "title" => "Withdrawal request",
                    "message" => "New Withdrawal Request from " . $user->getFullname(),
                ]
            );
            
            flash()->success("Withdrawal request submitted successfully.");
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the withdrawal form template.
        return $this->render('dashboard/withdrawal.html.twig');
    }


    #[Route('/signal', name: 'signal')]
    public function signal(Request $request, EntityManagerInterface $entityManager, FlasherInterface $flasher): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        if (!$user) {
            flash()->error("You must be logged in to activate a signal.");
            return $this->redirectToRoute('app_login');
        }
        
        // Process the form submission.
        if ($request->isMethod('POST') && $request->request->has('signal_activation')) {
            // Retrieve the payment method from the form.
            $depositMethod = $request->request->get('depositmethod');
            // The Amount field is disabled in the form so we set it to 0.
            $amount = 0;
            
            // (Optional) You can check if the user has sufficient balance if needed.
            // For signal activation, we assume the amount is 0.
            
            // Handle file upload for the signal activation proof.
            $signalProofFile = $request->files->get('SigAcProof');
            $proofFilename = null;
            if ($signalProofFile) {
                // Ensure you have defined the "uploads_signal_proofs" parameter (e.g., in config/services.yaml)
                $uploadDir = $this->getParameter('uploads_signal_proofs');
                $proofFilename = uniqid('signal_', true) . '.' . $signalProofFile->guessExtension();
                try {
                    $signalProofFile->move($uploadDir, $proofFilename);
                } catch (\Exception $e) {
                    flash()->error("Failed to upload proof of payment. Please try again.");
                    return $this->redirectToRoute('signal');
                }
            }
            
            // Create new Signal entity.
            $signal = new Signal();
            $signal->setUser($user);
            $signal->setDepositMethod($depositMethod);
            $signal->setAmount((float)$amount);
            $signal->setSignalProof($proofFilename);
            $signal->setStatus("PENDING");  // Initial status.
            $signal->setCreatedAt(new \DateTime());
            $signal->setUpdatedAt(new \DateTime());
            
            // Persist the Signal entity.
            $entityManager->persist($signal);
            $entityManager->flush();

            $user = $entityManager->getRepository(User::class)->find($user);
            $this->emailSender->sendEmail(
                "fortune@fortunesportfolio.com",
                "New Signal Request",
                "email/noti.twig",
                [
                    "title" => "Signal request",
                    "message" => "New Signal Request from " . $user->getFullname(),
                ]
            );
            
            flash()->success("Signal activation request submitted successfully.");
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the Signal Activation form.
        return $this->render('dashboard/signal.html.twig');
    }

    #[Route('/upgrade', name: 'upgrade')]
    public function upgrade(Request $request, EntityManagerInterface $entityManager, FlasherInterface $flasher): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        if (!$user) {
            flash()->error("You must be logged in to request an upgrade.");
            return $this->redirectToRoute('app_login');
        }
        
        if ($request->isMethod('POST') && $request->request->has('uprequest')) {
            // Retrieve form fields.
            $upgradePlan   = $request->request->get('upgraderequest');
            $paymentMethod = $request->request->get('paymentmethod');
            $description   = $request->request->get('narration');
            
            // Validate required fields.
            if (!$upgradePlan || !$paymentMethod || !$description) {
                flash()->error("Please fill all required fields.");
                return $this->redirectToRoute('upgrade');
            }
            
            // Handle file upload for proof of payment.
            $proofFile = $request->files->get('requestProof');
            $proofFilename = null;
            if ($proofFile) {
                $uploadDir = $this->getParameter('uploads_upgrade_proofs');
                $proofFilename = uniqid('upgrade_', true) . '.' . $proofFile->guessExtension();
                try {
                    $proofFile->move($uploadDir, $proofFilename);
                } catch (\Exception $e) {
                    flash()->error("Failed to upload proof of payment. Please try again.");
                    return $this->redirectToRoute('upgrade');
                }
            }
            
            // Create a new Upgrade entity.
            $upgrade = new Upgrade();
            $upgrade->setUser($user);
            $upgrade->setUpgradePlan($upgradePlan);
            $upgrade->setPaymentMethod($paymentMethod);
            $upgrade->setProof($proofFilename);
            $upgrade->setDescription($description);
            $upgrade->setStatus("PENDING"); // Initial status.
            $upgrade->setCreatedAt(new \DateTime());
            $upgrade->setUpdatedAt(new \DateTime());
            
            // Persist the Upgrade entity.
            $entityManager->persist($upgrade);
            $entityManager->flush();

            $user = $entityManager->getRepository(User::class)->find($user);
            $this->emailSender->sendEmail(
                "fortune@fortunesportfolio.com",
                "New Upgrade Request",
                "email/noti.twig",
                [
                    "title" => "Upgrade request",
                    "message" => "New Upgrade Request from " . $user->getFullname(),
                ]
            );
            
            flash()->success("Upgrade request submitted successfully.");
            return $this->redirectToRoute('dashboard');
        }
        
        // Render the upgrade form template.
        return $this->render('dashboard/upgrade.html.twig');
    }

    #[Route('/earning', name: 'earning')]
    public function history(EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in user.
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Retrieve all trades for the user, ordered by execution date (newest first).
        $trades = $entityManager->getRepository(Trade::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        // Render the trade history template and pass the trades.
        return $this->render('dashboard/earning.html.twig', [
            'trades' => $trades,
        ]);
    }

    #[Route('/transactions', name: 'transactions')]
    public function transactions(EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in.
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Retrieve transactions for the logged-in user.
        $deposits = $entityManager->getRepository(Deposit::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        $withdrawals = $entityManager->getRepository(Withdrawal::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        $signals = $entityManager->getRepository(Signal::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        $trades = $entityManager->getRepository(Trade::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        return $this->render('dashboard/transactions.html.twig', [
            'deposits'    => $deposits,
            'withdrawals' => $withdrawals,
            'signals'     => $signals,
            'trades'      => $trades,
        ]);
    }

    #[Route('/ref', name: 'ref')]
    public function referralHistory(EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in.
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user);
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Get the current user's referral code from the "uid" field.
        $referralCode = $user->getUid();
        
        // Fetch all users whose refCodeId equals the current user's uid.
        // (Assuming the User entity has a field "refCodeId" with an appropriate getter.)
        $referrals = $entityManager->getRepository(User::class)->findBy([
            'ref_code' => $user->getUid()
        ]);
        
        // Each referral gives a fixed earning of $5.
        $totalEarnings = count($referrals) * 5;
        
        return $this->render('dashboard/ref.html.twig', [
            'referralCode' => $referralCode,
            'referrals'    => $referrals,
            'totalEarnings'=> $totalEarnings,
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        // Retrieve the currently logged-in user.
        $user = $this->getUser();
        if (!$user) {
            // Redirect to login if no user is logged in.
            return $this->redirectToRoute('app_login');
        }
        
        // Render the profile template. The user data is available via app.user in Twig.
        return $this->render('dashboard/profile.html.twig', [
            // Optionally pass additional variables if needed.
        ]);
    }


    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
