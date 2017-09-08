<?php
/**
 * Created by PhpStorm.
 * User: osboxes
 * Date: 21/06/17
 * Time: 18:37
 */

namespace Controller;

use Repository\StaticRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

/**
 * Class HelloController.
 *
 * @package Controller
 */
class StaticController implements ControllerProviderInterface
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
        $controller->get('/about', [$this, 'aboutAction'])
            ->bind('about');
        $controller->get('/rules', [$this, 'rulesAction'])
            ->bind('rules');

        return $controller;
    }


    /**
     * About action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function aboutAction(Application $app)
    {
        $staticRepository = new StaticRepository($app['db']);

        return $app['twig']->render(
            'about.html.twig',
            ['text' => $staticRepository->getText(3, 'pl')]
        );
    }

    /**
     * Rules action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function rulesAction(Application $app)
    {
        $staticRepository = new StaticRepository($app['db']);

        return $app['twig']->render(
            'rules.html.twig',
            [
                'text1' => $staticRepository->getText(1, 'pl'),
                'text2' => $staticRepository->getText(2, 'pl'),
            ]
        );
    }
}
