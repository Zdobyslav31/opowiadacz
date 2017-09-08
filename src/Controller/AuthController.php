<?php
/**
 * Auth controller.
 *
 */
namespace Controller;

use Form\LoginType;
use Form\RegisterType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Repository\UserRepository;
use Form\ChangePasswordUserType;

/**
 * Class AuthController
 *
 * @package Controller
 */
class AuthController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->match('login', [$this, 'loginAction'])
            ->method('GET|POST')
            ->bind('auth_login');
        $controller->get('logout', [$this, 'logoutAction'])
            ->bind('auth_logout');
        $controller->match('register', [$this, 'registerAction'])
            ->method('GET|POST')
            ->bind('auth_register');
        $controller->match('change_password_user', [$this, 'changepasswordAction'])
            ->method('GET|POST')
            ->bind('change_password_user');

        return $controller;
    }

    /**
     * Login action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function loginAction(Application $app, Request $request)
    {
        $user = ['login' => $app['session']->get('_security.last_username')];
        $formLogin = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();
        $formRegister = $app['form.factory']->createBuilder(RegisterType::class, $user)->getForm();

        return $app['twig']->render(
            'login.html.twig',
            [
                'form_login' => $formLogin->createView(),
                'form_register' => $formRegister->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }


    /**
     * Logout action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function logoutAction(Application $app)
    {
        $app['session']->clear();

        return $app['twig']->render('auth/logout.html.twig', []);
    }

    /**
     * Register action
     * @param Application $app
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function registerAction(Application $app, Request $request)
    {
        $user = [];
        $formLogin = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();
        $formRegister = $app['form.factory']->createBuilder(
            RegisterType::class,
            $user,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();
        $formRegister->handleRequest($request);

        if ($formRegister->isSubmitted() && $formRegister->isValid()) {
            $user  = $formRegister->getData();
            $userRepository = new UserRepository($app['db']);

            $userRepository->addUser($app, $user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.account_successfully_created',
                ]
            );

            return $app->redirect($app['url_generator']->generate('auth_login'), 301);
        }

        return $app['twig']->render(
            'login.html.twig',
            [
                'form_login' => $formLogin->createView(),
                'form_register' => $formRegister->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }

    /**
     * Change password action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function changePasswordAction(Application $app, Request $request)
    {
        $userRepository = new UserRepository($app['db']);


        $form = $app['form.factory']->createBuilder(ChangePasswordUserType::class)->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            if ($userRepository->changePassword($form->getData(), $app, $app['user']->getId())) {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.password_changed',
                    ]
                );

                return $app->redirect(
                    $app['url_generator']->generate('profile'),
                    301
                );
            }

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.bad_credentials',
                ]
            );
        }

        return $app['twig']->render(
            'admin/changepassword.html.twig',
            [
                'role' => 'user',
                'form' => $form->createView(),
            ]
        );
    }
}
