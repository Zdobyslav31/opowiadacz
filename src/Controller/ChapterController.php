<?php
/**
 * Created by PhpStorm.
 * User: osboxes
 * Date: 21/06/17
 * Time: 18:37
 */

namespace Controller;

use Repository\ChapterRepository;
use Repository\TreeRepository;
use Repository\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Form\ChapterForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class HelloController.
 *
 * @package Controller
 */
class ChapterController implements ControllerProviderInterface
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
            ->bind('homepage');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('homepage_paginated');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('chapter_view');
        $controller->match('/{parentId}/add', [$this, 'addAction'])
            ->method('GET|POST')
            ->assert('parentId', '[1-9]\d*')
            ->bind('chapter_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('chapter_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('chapter_delete');
        $controller->match('/indexByUser', [$this, 'indexByUserAction'])
            ->bind('profile');
        $controller->get('/indexByUser/{page}', [$this, 'indexByUserAction'])
            ->value('page', 1)
            ->bind('indexByUser_paginated');

        return $controller;
    }


    /**
     * Add action.
     *
     * @param \Silex\Application                        $app      Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request  HTTP Request
     * @param int                                       $parentId
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function addAction(Application $app, Request $request, $parentId)
    {
        $chapter = [];
        $treeRepository = new TreeRepository($app['db']);
        $chapter['content'] = 'Tu napisz swój rozdział';

        $form = $app['form.factory']->createBuilder(ChapterForm::class, $chapter)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapterRepository = new ChapterRepository($app['db']);
            if (empty($chapterRepository->findOneById($parentId))) {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'danger',
                        'message' => 'message.parent_deleted',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('homepage'), 301);
            }

            $newId = $chapterRepository->save($form->getData(), $app);
            $treeRepository->addElement($parentId, $newId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('chapter_view', ['id' => $newId]), 301);
        }

        return $app['twig']->render(
            'chapter/add.html.twig',
            [
                'parent_id' => $parentId,
                'chapter' => $chapter,
                'form' => $form->createView(),
                'summaries' => $treeRepository->findCurrentPlotSummary($parentId),
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
        $chapterRepository = new ChapterRepository($app['db']);
        $treeRepository = new TreeRepository($app['db']);
        $chapter = $chapterRepository->findOneById($id);

        if (!$chapter) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $role = $app['user']->getRoles();
        if (!($chapter['author'] == $app['user']->getUsername() || $role[0] == 'ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.no_access',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        if ($chapter['has_children']) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.cannot_delete_has_children',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $chapter)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapter = $chapterRepository->findOneById($id);
            if ($chapter['has_children']) {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'warning',
                        'message' => 'message.cannot_delete_has_children',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('homepage'));
            }
            $treeRepository->removeElement($id);
            $chapterRepository->delete($form->getData());


            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('homepage'),
                301
            );
        }

        return $app['twig']->render(
            'chapter/delete.html.twig',
            [
                'chapter' => $chapter,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param int                                       $id      Record id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function editAction(Application $app, Request $request, $id)
    {
        $chapterRepository = new ChapterRepository($app['db']);
        $chapter = $chapterRepository->findOneById($id);

        if (!$chapter) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $role = $app['user']->getRoles();
        if (!($chapter['author'] == $app['user']->getUsername() || $role[0] == 'ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.no_access',
                ]
            );

            return $app->redirect($app['url_generator']->generate('chapter_view', ['id' => $id]), 301);
        }

        $form = $app['form.factory']->createBuilder(ChapterForm::class, $chapter)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapterRepository->save($form->getData(), $app);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('chapter_view', ['id' => $id]), 301);
        }

        return $app['twig']->render(
            'chapter/edit.html.twig',
            [
                'chapter' => $chapter,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app, $page = 1)
    {
        $chapterRepository = new ChapterRepository($app['db']);

        return $app['twig']->render(
            'chapter/index.html.twig',
            ['paginator' => $chapterRepository->findAllPaginated($page)]
        );
    }

    /**
     * IndexByUser action.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexByUserAction(Application $app, $page = 1)
    {
        $chapterRepository = new ChapterRepository($app['db']);


        return $app['twig']->render(
            'profile.html.twig',
            ['paginator' => $chapterRepository->findByUserPaginated($app, $page)]
        );
    }


    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     * @param string             $id  Element Id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function viewAction(Application $app, $id)
    {
        $chapterRepository = new ChapterRepository($app['db']);
        $treeRepository = new TreeRepository($app['db']);
        $chapter = $chapterRepository->findOneById($id);

        if (!$chapter) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $parentId = $treeRepository->findParentId($id);

        return $app['twig']->render(
            'chapter/view.html.twig',
            [
                'chapter' => $chapter,
                'previous_chapters' => $treeRepository->findCurrentPlotSummary($parentId),
                'parent_id' => $parentId,
                'children' => $treeRepository->findChildren($id),
            ]
        );
    }
}
