<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Security\LoginFormAuthenticator;
use App\Service\MailerService;
use DateTime;

#[Route('/home')]
final class HomeController extends AbstractController
{

    private $emailSender;



    public function __construct(MailerService $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
    ): JsonResponse {
        // Decode the JSON payload
        $data = $request->request->all();
        //dd($data);
        // Ensure required fields are provided
        if (
            !isset($data['fullname'], $data['username'], $data['email'],
                   $data['password'], $data['confirm_password'])
        ) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'wrong',
                'message' => 'Missing required fields.'
            ], JsonResponse::HTTP_OK);
        }

        // Check if passwords match
        if ($data['password'] !== $data['confirm_password']) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'wrong',
                'message' => 'Password and confirmation do not match.'
            ], JsonResponse::HTTP_OK);
        }

        // Verify if a user with the same email exists
        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'exists',
                'message' => 'Email already exists.',
                'field'   => 'email'
            ], JsonResponse::HTTP_OK);
        }

        // Verify if a user with the same username exists
        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'exists',
                'message' => 'Username already exists.',
                'field'   => 'username'
            ], JsonResponse::HTTP_OK);
        }

        // Create new security-enabled User entity
        $user = new User();
        $user->setFullname($data['fullname']);
        $user->setUsername($data['username']);
        $user->setCountry($data['country'] ?? null);
        $user->setCurrency($data['currency'] ?? null);
        $user->setEmail($data['email']);
        $user->setUid($this->generateRandom8DigitId());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setVisiblepassword($data['password']);
        // Hash the password securely
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $user->setSocialAccount($data['social_account'] ?? null);
        $user->setSocialAccountContact($data['social_account_contact'] ?? null);
        $refUser = $entityManager->getRepository(User::class)->findOneBy(["uid" => $data['ref_code']]);
        if ($refUser) {
            // If found, set the ref code to the UID or any other property as needed
            $user->setRefCode($refUser->getId());
        } else {
            // Optionally, handle the case when no referring user is found.
            // For example, you could log a warning or set the ref code to null.
            $user->setRefCode(null);
        }

        // Validate the User entity (using validation constraints if defined)
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'wrong',
                'message' => implode(', ', $errorMessages)
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Persist the new user to the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Authenticate the new user immediately
        // $userAuthenticator->authenticateUser(
        //     $user,
        //     $authenticator,
        //     $request
        // );
        try {
            $this->emailSender->sendEmail($user->getEmail(), "Welcome OnBoard", "email/welcome.twig", [
                "name" => $user->getFullname(),
                "useremail" => $user->getEmail(),
                "ref" => $user->getUid(),
                "date" => $user->getCreatedat()->format('Y-m-d'),
            ]);
            $this->emailSender->sendEmail(
               "fortune@fortunesportfolio.com",
                "New User Registration",
                "email/noti.twig",
                [
                    "title" => "New User Registration",
                    "message" => "New Registration From User: " . $user->getFullname(),
                ]
            );

        } catch (\Throwable $th) {
            throw $th;
        }

        return new JsonResponse([
            'error'   => false,
            'notice'  => 'successful',
            'message' => 'User registered and logged in successfully.'
        ], JsonResponse::HTTP_CREATED);
    }


    #[Route('/mainlogin', name: 'mainlogin')]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator
    ): JsonResponse {
        // Retrieve form data (assumes form-encoded data, not JSON)
        $data = $request->request->all();
        // dd($data);
        // Extract credentials
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'not_found',
                'message1'=> 'Missing credentials.',
                'role'    => 'user',
            ], 200);
        }

        // Attempt to find the user by username
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'not_found',
                'message1'=> 'Account not found.',
                'role'    => 'user',
            ], 200);
        }

        // (Optional) Check if account is deleted/inactive.
        // Assuming your User entity implements isDeleted() or isActive() as needed.
        if (method_exists($user, 'isDeleted') && $user->isDeleted()) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'deleted',
                'message' => 'This account has been deleted.',
                'role'    => 'user',
            ], JsonResponse::HTTP_OK);
        }

        // Validate the provided password
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'error'   => true,
                'notice'  => 'no_match',
                'message' => 'Incorrect password.',
                'role'    => in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'user',
            ], JsonResponse::HTTP_OK);
        }

        // (Optional) Check if the user has verified their account.
        // Assuming your User entity implements isVerified() if verification is required.
        if (method_exists($user, 'isVerified') && !$user->isVerified()) {
            return new JsonResponse([
                'error'   => false,
                'notice'  => 'verify',
                'message' => 'Please verify your account before logging in.',
                'role'    => in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'user',
            ], JsonResponse::HTTP_OK);
        }

        // Determine role string based on user roles
        $role = in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'user';

        // Immediately authenticate the user
        $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        // // Determine the redirect URL based on role (for client-side handling)
        $redirectUrl = ($role === 'admin') ? 'admin' : '/dashboard';

        // Return a JSON response containing login details for the client-side
        return new JsonResponse([
            'error'    => false,
            'notice'   => 'successful',
            'message'  => 'User logged in successfully.',
            'role'     => $role,
            'redirect' => $redirectUrl,
        ], JsonResponse::HTTP_OK);
    }

    function generateRandom8DigitId(): int {
        // The minimum 8-digit integer is 10,000,000 and the maximum is 99,999,999.
        return random_int(10000000, 99999999);
    }
}
