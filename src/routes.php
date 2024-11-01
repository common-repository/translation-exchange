<?php

/**
 * Copyright (c) 2017 Translation Exchange, Inc. https://translationexchange.com
 *
 *  _______                  _       _   _             ______          _
 * |__   __|                | |     | | (_)           |  ____|        | |
 *    | |_ __ __ _ _ __  ___| | __ _| |_ _  ___  _ __ | |__  __  _____| |__   __ _ _ __   __ _  ___
 *    | | '__/ _` | '_ \/ __| |/ _` | __| |/ _ \| '_ \|  __| \ \/ / __| '_ \ / _` | '_ \ / _` |/ _ \
 *    | | | | (_| | | | \__ \ | (_| | |_| | (_) | | | | |____ >  < (__| | | | (_| | | | | (_| |  __/
 *    |_|_|  \__,_|_| |_|___/_|\__,_|\__|_|\___/|_| |_|______/_/\_\___|_| |_|\__,_|_| |_|\__, |\___|
 *                                                                                        __/ |
 *                                                                                       |___/
 * GNU General Public License, version 2
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (has_action('rest_api_init')) {

    /**
     * Get role based on action
     *
     * @param $action
     * @return int|string
     */
    function trex_get_role($action) {
        $roles = array(
            "public" => array('get-strategy', 'get-auth-methods', 'get-languages'),
            "editor" => array(),
            "admin" => array('post-webhooks', 'delete-webhooks')
        );

        foreach($roles as $role => $actions) {
            if (in_array($action, $actions))
                return $role;
        }

        return 'editor';
    }

    /**
     * Check permissions
     *
     * @param $action
     * @return bool
     */
    function trex_check_permission($action) {
        $role = trex_get_role($action);
        if ($role == 'public')
            return true;

        $permissions = array(
            "editor" => array("edit_pages"),
            "admin"  => array("activate_plugins")
        )[$role];

        foreach($permissions as $permission) {
            if (!current_user_can($permission))
                return false;
        }

        return true;
    }

    /**
     * Setup routes
     */
    add_action('rest_api_init', function () {

        register_rest_route('trex/v1', '/strategy', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return array('strategy' => $trex_api_strategy->getName());
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-strategy');
            }
        ));

        register_rest_route('trex/v1', '/auth/methods', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $methods = array('Basic Auth');

                    if (is_plugin_active('rest-api-oauth1/oauth-server.php')) {
                        array_push($methods, 'OAuth 1');
                    }

                    if (is_plugin_active('oauth2-provider/wp-oauth.php')) {
                        array_push($methods, 'OAuth 2');
                    }

                    return array('methods' => $methods);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-auth-methods');
            }
        ));

        register_rest_route('trex/v1', '/webhooks', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    if (isset($params['webhooks'])) {
                        update_option('trex_api_webhooks', $params['webhooks']);
                        error_log(var_export(get_option('trex_api_webhooks'), true));
                    }
                    return array("status" => "Ok");
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-webhooks');
            }
        ));

        register_rest_route('trex/v1', '/webhooks', array(
            'methods' => 'DELETE',
            'callback' => function ($params) {
                try {
                    delete_option('trex_api_webhooks');
                    return array("status" => "Ok");
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('delete-webhooks');
            }
        ));

        register_rest_route('trex/v1', '/languages', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getLanguages($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-languages');
            }
        ));

        register_rest_route('trex/v1', '/languages', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->addLanguages($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-languages');
            }
        ));

        register_rest_route('trex/v1', '/languages/default', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getDefaultLanguage($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-languages-default');
            }
        ));

        register_rest_route('trex/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPosts($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-posts');
            }
        ));

        register_rest_route('trex/v1', '/posts/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPost($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-post');
            }
        ));

        register_rest_route('trex/v1', '/posts/(?P<id>\d+)/translations', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPostTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-post-translations');
            }
        ));

        register_rest_route('trex/v1', '/posts/(?P<id>\d+)/translations', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    global $disable_webhooks;
                    $disable_webhooks = true;
                    global $trex_api_strategy;
                    return $trex_api_strategy->postTranslations($params, 'post');
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-post-translations');
            }
        ));

        register_rest_route('trex/v1', '/pages', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPages($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-pages');
            }
        ));

        register_rest_route('trex/v1', '/pages/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPage($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-page');
            }
        ));

        register_rest_route('trex/v1', '/pages/(?P<id>\d+)/translations', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    global $trex_api_strategy;
                    return $trex_api_strategy->getPageTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-page-translations');
            }
        ));

        register_rest_route('trex/v1', '/pages/(?P<id>\d+)/translations', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    global $disable_webhooks;
                    $disable_webhooks = true;
                    global $trex_api_strategy;
                    return $trex_api_strategy->postTranslations($params, 'page');
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-page-translations');
            }
        ));

        register_rest_route('trex/v1', '/themes', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new ThemeManager();
                    return $manager->getItems($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-themes');
            }
        ));

        register_rest_route('trex/v1', '/themes/(?P<key>[a-z_\-\d]+)', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new ThemeManager();
                    return $manager->getItem($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-theme');
            }
        ));

        register_rest_route('trex/v1', '/themes/(?P<key>[a-z_\-\d]+)/template', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new ThemeManager();
                    return $manager->getTemplate($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-theme-template');
            }
        ));

        register_rest_route('trex/v1', '/themes/(?P<key>[a-z_\-\d]+)/translations', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new ThemeManager();
                    return $manager->getTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-theme-translations');
            }
        ));

        register_rest_route('trex/v1', '/themes/(?P<key>[a-z_\-\d]+)/translations', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    $manager = new ThemeManager();
                    return $manager->postTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-theme-translations');
            }
        ));


        register_rest_route('trex/v1', '/plugins', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new PluginManager();
                    return $manager->getItems($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-plugins');
            }
        ));

        register_rest_route('trex/v1', '/plugins/(?P<key>[a-z_\-\d]+)', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new PluginManager();
                    return $manager->getItem($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-plugin');
            }
        ));

        register_rest_route('trex/v1', '/plugins/(?P<key>[a-z_\-\d]+)/template', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new PluginManager();
                    return $manager->getTemplate($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-plugin-template');
            }
        ));

        register_rest_route('trex/v1', '/plugins/(?P<key>[a-z_\-\d]+)/translations', array(
            'methods' => 'GET',
            'callback' => function ($params) {
                try {
                    $manager = new PluginManager();
                    return $manager->getTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('get-plugin-translations');
            }
        ));

        register_rest_route('trex/v1', '/plugins/(?P<key>[a-z_\-\d]+)/translations', array(
            'methods' => 'POST',
            'callback' => function ($params) {
                try {
                    $manager = new PluginManager();
                    return $manager->postTranslations($params);
                } catch (Exception $ex) {
                    return array('status' => 'error', 'message' => $ex->getMessage());
                }
            },
            'permission_callback' => function () {
                return trex_check_permission('post-plugin-translations');
            }
        ));

    });
}
