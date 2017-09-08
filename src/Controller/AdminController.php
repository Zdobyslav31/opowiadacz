<?php
/**
 * Created by PhpStorm.
 * User: osboxes
 * Date: 21/06/17
 * Time: 18:37
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Repository\AdminRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Form\ChangePasswordAdminType;

/**
 * Class HelloController.
 *
 * @package Controller
 */
class AdminController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])
            ->bind('admin');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('admin_paginated');
        $controller->match('/{id}/changePermissions', [$this, 'changePermissionsAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('change_permissions');
        $controller->match('/{id}/changePassword', [$this, 'changePasswordAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('change_password');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('user_delete');

        return $controller;
    }


    /**
     * ChangePassword action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param int                                       $id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function changePasswordAction(Application $app, Request $request, $id)
    {
        $adminRepository = new AdminRepository($app['db']);
        $user = $adminRepository->findOneById($id);


        if (empty($user['id'])) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }


        $form = $app['form.factory']->createBuilder(ChangePasswordAdminType::class, $user)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminRepository->changePassword($id, $form->getData(), $app);


            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.password_changed',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('admin'),
                301
            );
        }

        return $app['twig']->render(
            'admin/changepassword.html.twig',
            [
                'role' => 'admin',
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Change Permissions action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param int                                       $id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function changePermissionsAction(Application $app, Request $request, $id)
    {
        $adminRepository = new AdminRepository($app['db']);
        $user = $adminRepository->findOneById($id);


        if (empty($user['id'])) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $user)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminRepository->changePermissions($form->getData());


            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.permissions_changed',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('admin'),
                301
            );
        }

        return $app['twig']->render(
            'admin/changepermissions.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param int                                       $id      Record id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function deleteAction(Application $app, Request $request, $id)
    {
        $adminRepository = new AdminRepository($app['db']);
        $user = $adminRepository->findOneById($id);


        if (empty($user['id'])) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $user)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminRepository->delete($form->getData());


            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('admin'),
                301
            );
        }

        return $app['twig']->render(
            'admin/delete.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page Page number
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app, $page = 1)
    {
        $adminRepository = new AdminRepository($app['db']);
        $paginator = $adminRepository->findUsersWithCHapters($page);


        return $app['twig']->render(
            'admin/index.html.twig',
            ['paginator' => $paginator]
        );
    }
}
