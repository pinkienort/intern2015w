<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */
final class add_room
{
    function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ( isset($app->post['slug']) ){
            $is_daburi = self::isTyouhuku($app->post['slug']);
        }

        if (!$is_daburi && isset($app->post['slug'], $app->post['name'])
            && self::regist($app->post['slug'], $app->post['name'], $app->getLoginUser())
        ) {
            return new Response\RedirectResponse('/rooms/' . $app->post['slug']);
        }

        return new Response\RedirectResponse('/');
    }

    private static function isTyouhuku(string $slug): bool
    {
        $query = "SELECT * FROM `rooms` WHERE `slug` = \"${slug}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $user): bool
    {
        $query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $id = db()->lastInsertId();

        $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
        $user_name = $user->name;
        $message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
        $query = "INSERT INTO `posts` VALUES( {$id}, 0, \"{$now}\", \"{$message}\" )";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return true;
    }
}
