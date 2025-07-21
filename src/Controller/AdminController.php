<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardTransaction;
use App\Entity\Deposit;
use App\Entity\Investment;
use App\Entity\Signal;
use App\Entity\Trade;
use App\Entity\Transaction;
use App\Entity\Upgrade;
use App\Entity\User;
use App\Entity\Withdrawal;
use App\Service\MailerService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private $emailSender;

    public function __construct(MailerService $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    #[Route('/', name: 'admin')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // Ensure admin privileges.
     

        // Fetch all users.
        $users = $doctrine->getRepository(User::class)->findAll();

        // Fetch pending transactions for each type.
        $pendingDeposits     = $doctrine->getRepository(Deposit::class)->findBy(['status' => 'pending']);
        $pendingWithdrawals  = $doctrine->getRepository(Withdrawal::class)->findBy(['status' => 'pending']);
        $pendingUpgrades     = $doctrine->getRepository(Upgrade::class)->findBy(['status' => 'pending']);
        $pendingSignals      = $doctrine->getRepository(Signal::class)->findBy(['status' => 'pending']);
        $pendingTrades       = $doctrine->getRepository(Trade::class)->findBy(['status' => 'pending']);

        return $this->render('admin/index.html.twig', [
            'users'               => $users,
            'pendingDeposits'     => $pendingDeposits,
            'pendingWithdrawals'  => $pendingWithdrawals,
            'pendingUpgrades'     => $pendingUpgrades,
            'pendingSignals'      => $pendingSignals,
            'pendingTrades'       => $pendingTrades,
        ]);
    }
 /**
     * @Route("/admin/user/{id}/verify", name="admin_verify_user", methods={"GET"})
     */
    #[Route('/user/{id}/verify', name: 'admin_verify_user')]
    public function verifyUser(ManagerRegistry $doctrine, int $id): Response
    {
        // Ensure the current user has admin privileges.
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            flash()->error("User not found.");
            return $this->redirectToRoute('admin');
        }

        // Check if the user is already verified.
        if ($user->isVerified()) {
            flash()->error("User is already verified.");
            return $this->redirectToRoute('admin');
        }

        // Mark the user as verified.
        $user->setIsVerified(true);
        $entityManager->flush();

        flash()->success("User verified successfully.");
        return $this->redirectToRoute('admin');
    }
    #[Route('/profile/{id}', name: 'profileview', methods: ['GET','POST'])]
    public function profile(string $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        
        // 1) Retrieve the user by ID
        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin');
        }

        // 2) Update user fields if "update" was submitted
        if ($request->request->get('update') !== null) {
            // Example of updating only the fields that exist in your User entity
            $user
                ->setFullName($request->request->get('fullname', $user->getFullName()))
                ->setEmail($request->request->get('email', $user->getEmail()))
                ->setCountry($request->request->get('country', $user->getCountry()))
                ->setCurrency($request->request->get('currency', $user->getCurrency()))
                // numeric fields that exist in your User entity
                ->setBalance((float) $request->request->get('balance', $user->getBalance() ?? 0))
                ->setEarning((float) $request->request->get('earning', $user->getEarning() ?? 0))
                ->setPendingWithdrawal((float) $request->request->get('pending_withdrawal', $user->getPendingWithdrawal() ?? 0))
                ->setActiveDeposits((float) $request->request->get('active_deposits', $user->getActiveDeposits() ?? 0))
                ->setLastDeposit((float) $request->request->get('last_deposit', $user->getLastDeposit() ?? 0))
                ;

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile was updated successfully.');
            return $this->redirectToRoute('admin');
        }

        // 3) Toggle isVerified if "activate" was submitted
        if ($request->request->get('activate') !== null) {
            $user->setIsVerified(!$user->isVerified());
            $em->persist($user);
            $em->flush();

            // If you want to send an email upon deactivation, for example:
            if (!$user->isVerified()) {
                // Pseudo-code if you have an email sender service
                $this->emailSender->sendEmail($user->getEmail(), "Account De-Activated", "email/noti.twig", [
                    "title" => "Account De-Activated",
                    "message" => "Dear {$user->getFullName()}, your account has been de-activated, please contact support."
                ]);
            }

            $this->addFlash('success', 'User verification status updated successfully.');
            return $this->redirectToRoute('admin');
        }

        // 4) Delete the user if "delete" was submitted
        if ($request->request->get('delete') !== null) {
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'User successfully deleted.');
            return $this->redirectToRoute('admin');
        }

        // 5) (Optional) Add a deposit transaction if "adddeposit" was submitted
        if ($request->request->get('adddeposit') !== null) {
            $amount = (float) $request->request->get('amount', 0);
        
            // Create and persist a new Deposit entity
            $deposit = new Deposit();
            $deposit
                ->setUser($user)
                ->setAmount($amount)
                ->setStatus('approved')
                ->setCreatedAt(new DateTime());
        
            // Update user fields: balance, lastDeposit, activeDeposits
            $user->setBalance(($user->getBalance() ?? 0) + $amount)
                 ->setLastDeposit($amount)
                 ->setActiveDeposits(($user->getActiveDeposits() ?? 0) + $amount);
        
            $em->persist($deposit);
            $em->persist($user);
            $em->flush();
        
            $this->addFlash('success', 'Deposit transaction successfully added and user balance updated.');
            return $this->redirectToRoute('admin');
        }

        // 6) Render the profile template
        return $this->render('admin/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/withdrawallist', name: 'withdrawallist')]
    public function withdrawals(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender
    ): Response {
        // Only fetch transactions that are "pending" AND presumably of type "withdrawal".
        // If the "Transaction" entity uses a 'type' field to differentiate deposit/withdrawal,
        // you can also add `->findBy(["status" => "pending", "type" => "withdrawal"])`
        $withdrawals = $doctrine->getRepository(Withdrawal::class)
            ->findBy(["status" => "pending"]);

        $em = $doctrine->getManager();

        // Approve Transaction
        if ($request->request->get('approve') !== null) {
            $transactionId = $request->request->get('id');
            $transaction = $doctrine->getRepository(Withdrawal::class)->find($transactionId);

            if (!$transaction) {
                $this->addFlash('error', 'Transaction not found.');
                return $this->redirectToRoute('withdrawallist');
            }

            // Mark transaction as complete and set the date
            $transaction->setStatus('complete')
                        ->setCreatedAt(new DateTime($request->request->get("date")));

            // Update user’s balance if needed
            $user = $transaction->getUser();
            $amount = $transaction->getAmount();
           

            // Persist changes
            $em->persist($user);
            $em->persist($transaction);
            $em->flush();

            // Send an email notification if desired
            $emailSender->sendEmail($user->getEmail(), "Withdrawal Complete", "email/noti.twig", [
                "title" => "Transaction Complete",
                "message" => $user->getFullname() . ", your transaction of $" . $amount . " has been approved successfully.",
            ]);

            $this->addFlash('success', "Transaction was successfully approved.");
            return $this->redirectToRoute('withdrawallist');
        }

        // Decline Transaction
        if ($request->request->get('delete') !== null) {
            $transactionId = $request->request->get('id');
            $transaction = $doctrine->getRepository(Withdrawal::class)->find($transactionId);

            if (!$transaction) {
                $this->addFlash('error', 'Transaction not found.');
                return $this->redirectToRoute('withdrawallist');
            }

            $user = $transaction->getUser();
            $amount = $transaction->getAmount();
            $user->setBalance(max(0, ($user->getBalance() ?? 0) + $amount));

            $transaction->setStatus('failed')
                        ->setCreatedAt(new DateTime($request->request->get("date")));
            $em->persist($transaction);
            $em->persist($user);
           
            $em->flush();

            $this->addFlash('error', "Transaction was successfully declined.");
            return $this->redirectToRoute('admin');
        }

        // Render the list of pending withdrawals
        return $this->render('admin/withdrawals.html.twig', [
            'withdrawals' => $withdrawals,
        ]);
    }
    #[Route('/depositlist', name: 'depositlist')]
    public function depositList(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender
    ): Response {
        // Fetch all deposits with status = "pending"
        $deposits = $doctrine->getRepository(Deposit::class)->findBy(['status' => 'pending']);
        
        $em = $doctrine->getManager();
        
        // Approve Deposit
        if ($request->request->get('approve') !== null) {
            $depositId = $request->request->get('id');
            $deposit = $doctrine->getRepository(Deposit::class)->find($depositId);

            if (!$deposit) {
                $this->addFlash('error', 'Deposit not found.');
                return $this->redirectToRoute('depositlist');
            }

            // Mark deposit as complete and set the date
            $deposit->setStatus('complete')
                    ->setCreatedAt(new DateTime($request->request->get('date'))); // If your deposit entity uses createdAt or another date field

            // Optionally update the user's balance or other fields
            $user = $deposit->getUser();
            $amount = $deposit->getAmount();
            if ($user) {
                // If you want to add the deposit amount to the user’s balance
                $user->setBalance(($user->getBalance() ?? 0) + $amount);
                $em->persist($user);
            }

            $em->persist($deposit);
            $em->flush();

            // Send an email notification if desired
            $emailSender->sendEmail(
                $user->getEmail(),
                "Deposit Complete",
                "email/noti.twig",
                [
                    "title" => "Deposit Complete",
                    "message" => $user->getFullname() . ", your deposit of $" . $amount . " has been approved successfully.",
                ]
            );

            $this->addFlash('success', "Deposit was successfully approved.");
            return $this->redirectToRoute('depositlist');
        }

        // Decline Deposit
        if ($request->request->get('delete') !== null) {
            $depositId = $request->request->get('id');
            $deposit = $doctrine->getRepository(Deposit::class)->find($depositId);

            if (!$deposit) {
                $this->addFlash('error', 'Deposit not found.');
                return $this->redirectToRoute('depositlist');
            }

            // Mark as failed
            $deposit->setStatus('failed')
                    ->setCreatedAt(new DateTime($request->request->get('date'))); // if you want to store the date of failure

            // If you want to revert something, do so here
            // For example, if you had previously added to user’s balance, you could revert it. 
            // But typically, a pending deposit would not yet have been added to the user’s final balance.

            $em->persist($deposit);
            $em->flush();

            $this->addFlash('error', "Deposit was successfully declined.");
            return $this->redirectToRoute('admin');
        }

        // Render the list of pending deposits
        return $this->render('admin/deposits.html.twig', [
            'deposits' => $deposits,
        ]);
    }
    #[Route('/upgradelist', name: 'upgradelist')]
    public function upgradeList(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender // or whichever mailer you use
    ): Response {
        // Fetch all Upgrade records with status = "pending".
        $pendingUpgrades = $doctrine->getRepository(Upgrade::class)
            ->findBy(['status' => 'pending']);

        $em = $doctrine->getManager();

        // Approve an upgrade request
        if ($request->request->get('approve') !== null) {
            $upgradeId = $request->request->get('id');
            $upgrade = $doctrine->getRepository(Upgrade::class)->find($upgradeId);

            if (!$upgrade) {
                $this->addFlash('error', 'Upgrade request not found.');
                return $this->redirectToRoute('upgradelist');
            }

            // Mark as complete and update date/time
            $upgrade->setStatus('complete')
                    ->setUpdatedAt(new DateTime($request->request->get('date')));

            // Optionally, update user fields or perform other actions.
            $user = $upgrade->getUser();
            if ($user) {
                // For example, if an upgrade might set the user to a "premium" role or something:
                // $roles = $user->getRoles();
                // $roles[] = 'ROLE_PREMIUM';
                // $user->setRoles(array_unique($roles));

                $em->persist($user);
            }

            $em->persist($upgrade);
            $em->flush();

            // Optionally send an email notification
            $emailSender->sendEmail(
                $user->getEmail(),
                "Account Upgrade Approved",
                "email/noti.twig",
                [
                    "title" => "Upgrade Approved",
                    "message" => $user->getFullname() . ", your upgrade to plan '" . $upgrade->getUpgradePlan() . "' has been approved successfully.",
                ]
            );

            $this->addFlash('success', "Upgrade was successfully approved.");
            return $this->redirectToRoute('upgradelist');
        }

        // Decline an upgrade request
        if ($request->request->get('delete') !== null) {
            $upgradeId = $request->request->get('id');
            $upgrade = $doctrine->getRepository(Upgrade::class)->find($upgradeId);

            if (!$upgrade) {
                $this->addFlash('error', 'Upgrade request not found.');
                return $this->redirectToRoute('upgradelist');
            }

            $upgrade->setStatus('failed')
                    ->setUpdatedAt(new DateTime($request->request->get('date')));

            // Optionally revert user changes if you made any on "approve".
            $em->persist($upgrade);
            $em->flush();

            $this->addFlash('error', "Upgrade request was declined.");
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/upgrades.html.twig', [
            'upgrades' => $pendingUpgrades,
        ]);
    }
   

        #[Route('/kyc', name: 'kyc')]
    public function upgradeList(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender // or whichever mailer you use
    ): Response {
        // Fetch all Upgrade records with status = "pending".
        $pendingKyc = $doctrine->getRepository(User::class)
            ->findBy(['verificationStatus' => 'PENDING']);

        $em = $doctrine->getManager();

        // Approve an upgrade request
        if ($request->request->get('approve') !== null) {
            $id = $request->request->get('id');
            $user = $doctrine->getRepository(User::class)->find($id);

            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('kyc');
            }

            // Mark as complete and update date/time
            $user->setVerificationStatus('COMPLETE')
                  ->setUpdatedAt(new DateTime($request->request->get('date')));

            $em->persist($user);
            $em->flush();

            // Optionally send an email notification
            $emailSender->sendEmail(
                $user->getEmail(),
                "Account Verification Approved",
                "email/noti.twig",
                [
                    "title" => "Verification Approved",
                    "message" => $user->getFullname() . ", your account verification has been approved successfully.",
                ]
            );

            $this->addFlash('success', "Verification was successfully approved.");
            return $this->redirectToRoute('kyc');
        }

        // Decline an upgrade request
        // if ($request->request->get('delete') !== null) {
        //     $upgradeId = $request->request->get('id');
        //     $upgrade = $doctrine->getRepository(Upgrade::class)->find($upgradeId);

        //     if (!$upgrade) {
        //         $this->addFlash('error', 'Upgrade request not found.');
        //         return $this->redirectToRoute('upgradelist');
        //     }

        //     $upgrade->setStatus('failed')
        //             ->setUpdatedAt(new DateTime($request->request->get('date')));

        //     // Optionally revert user changes if you made any on "approve".
        //     $em->persist($upgrade);
        //     $em->flush();

        //     $this->addFlash('error', "Upgrade request was declined.");
        //     return $this->redirectToRoute('admin');
        // }

        return $this->render('admin/kyc.html.twig', [
            'upgrades' => $pendingKyc,
        ]);
    }
   


    #[Route('/signallist', name: 'signallist')]
    public function signalList(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender // or whichever mailer you use
    ): Response {
        // Fetch all Signal records with status = "pending"
        $pendingSignals = $doctrine->getRepository(Signal::class)
            ->findBy(['status' => 'pending']);

        $em = $doctrine->getManager();

        // Approve a signal activation
        if ($request->request->get('approve') !== null) {
            $signalId = $request->request->get('id');
            $signal = $doctrine->getRepository(Signal::class)->find($signalId);

            if (!$signal) {
                $this->addFlash('error', 'Signal request not found.');
                return $this->redirectToRoute('signallist');
            }

            // Mark as complete, update updatedAt with the date provided
            $signal->setStatus('complete')
                   ->setUpdatedAt(new DateTime($request->request->get('date')));

            // Optionally, update the user or do something on approval
            $user = $signal->getUser();
            if ($user) {
                // e.g., if paying or awarding something for signal activation
                // $user->setBalance(($user->getBalance() ?? 0) + $signal->getAmount());
                $em->persist($user);
            }

            $em->persist($signal);
            $em->flush();

            // Optional email notification
            $emailSender->sendEmail(
                $user->getEmail(),
                "Signal Activation Approved",
                "email/noti.twig",
                [
                    "title" => "Signal Activation Complete",
                    "message" => $user->getFullname() . ", your signal activation has been approved successfully.",
                ]
            );

            $this->addFlash('success', "Signal activation was successfully approved.");
            return $this->redirectToRoute('signallist');
        }

        // Decline a signal activation
        if ($request->request->get('delete') !== null) {
            $signalId = $request->request->get('id');
            $signal = $doctrine->getRepository(Signal::class)->find($signalId);

            if (!$signal) {
                $this->addFlash('error', 'Signal request not found.');
                return $this->redirectToRoute('signallist');
            }

            $signal->setStatus('failed')
                   ->setUpdatedAt(new DateTime($request->request->get('date')));

            // If you want to revert any user changes, do it here
            // e.g. if previously adding to user's account on creation

            $em->persist($signal);
            $em->flush();

            $this->addFlash('error', "Signal activation was declined.");
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/signals.html.twig', [
            'signals' => $pendingSignals,
        ]);
    }
  
    #[Route('/tradelist', name: 'tradelist')]
    public function tradeList(
        ManagerRegistry $doctrine,
        Request $request,
        MailerService $emailSender // or whichever mailer you use
    ): Response {
        // Fetch all Trade records with status = "pending" (adjust if your status is spelled differently).
        $pendingTrades = $doctrine->getRepository(Trade::class)
            ->findBy(['status' => 'pending']);

        $em = $doctrine->getManager();

        // Approve a trade
        if ($request->request->get('approve') !== null) {
            $tradeId = $request->request->get('id');
            $trade = $doctrine->getRepository(Trade::class)->find($tradeId);

            if (!$trade) {
                $this->addFlash('error', 'Trade not found.');
                return $this->redirectToRoute('tradelist');
            }

            // Mark as complete, update updatedAt with the date provided.
            $trade->setStatus('complete')
                  ->setUpdatedAt(new DateTime($request->request->get('date')));

            // Optionally do something with the user: awarding, updating balance, etc.
            $user = $trade->getUser();
            if ($user) {
                // Example: if a trade completion might add an earning:
                // $user->setEarning(($user->getEarning() ?? 0) + 100.0);
                $em->persist($user);
            }

            $em->persist($trade);
            $em->flush();

            // Optional email notification
            $emailSender->sendEmail(
                $user->getEmail(),
                "Trade Approved",
                "email/noti.twig",
                [
                    "title" => "Trade Completed",
                    "message" => $user->getFullname() . ", your trade on " . $trade->getCurrencyPair() . " has been approved successfully.",
                ]
            );

            $this->addFlash('success', "Trade was successfully approved.");
            return $this->redirectToRoute('tradelist');
        }

        // Decline a trade
        if ($request->request->get('delete') !== null) {
            $tradeId = $request->request->get('id');
            $trade = $doctrine->getRepository(Trade::class)->find($tradeId);

            if (!$trade) {
                $this->addFlash('error', 'Trade not found.');
                return $this->redirectToRoute('tradelist');
            }

            $trade->setStatus('failed')
                  ->setUpdatedAt(new DateTime($request->request->get('date')));

            // Optionally revert user changes if you made them on approval
            $em->persist($trade);
            $em->flush();

            $this->addFlash('error', "Trade was declined.");
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/trades.html.twig', [
            'trades' => $pendingTrades,
        ]);
    }
}
