<?php
declare(strict_types=1);

use App\Application\Middleware\AppJsonMiddleware;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $container = $app->getContainer();

    $app->group('/users', function (Group $group) use ($container) {
        $group->get('', \App\Application\Actions\User\ListUsersAction::class);
        $group->post('', \App\Application\Actions\User\InsertUserAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->get('/{username}', \App\Application\Actions\User\ViewUserAction::class);
        $group->put('/{username}', \App\Application\Actions\User\UpdateUserAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->delete('/{username}', \App\Application\Actions\User\RemoveUserAction::class);
    });

    $app->group('/posts', function (Group $group) use ($container) {
        $group->get('', \App\Application\Actions\Post\ListPostsAction::class);
        $group->get('/{id}', \App\Application\Actions\Post\ViewPostAction::class);
        $group->post('', \App\Application\Actions\Post\InsertPostAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->put('/{id}', \App\Application\Actions\Post\UpdatePostAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->delete('/{id}', \App\Application\Actions\Post\RemovePostAction::class);

        /** LIKE */
        $group->post('/{id}/like/{username}', \App\Application\Actions\Post\LikedPostAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->delete('/{id}/like/{username}', \App\Application\Actions\Post\RemoveLikePostAction::class);

        /** TAGS */
        $group->post('/{id}/tag', \App\Application\Actions\Post\AddTagPostAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->delete('/{id}/tag/{tagname}', \App\Application\Actions\Post\RemoveTagPostAction::class);
    });

    $app->group('/tags', function (Group $group) use ($container) {
        $group->get('', \App\Application\Actions\Tag\ListTagsAction::class);
        $group->get('/{name}', \App\Application\Actions\Tag\ViewTagAction::class);
        $group->post('', \App\Application\Actions\Tag\InsertTagAction::class)->addMiddleware(new AppJsonMiddleware);
        $group->delete('/{name}', \App\Application\Actions\Tag\RemoveTagAction::class);
    });
};
