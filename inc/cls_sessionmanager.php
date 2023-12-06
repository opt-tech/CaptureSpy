<?php
    session_start();
    session_cache_limiter("public");


    /**
     * Created by PhpStorm.
     * User: kazuki kubota@castler
     * Date: 2016/07/22
     * Time: 3:21
     *
     * Ver 1.0  2016/07/22  新規作成
     */
    class SessionManager {
        var $name;


        public function SessionManager($name) {
            $this->name = $name;
        }


        public function load() {
            return $_SESSION[ $this->name ];
        }


        public function exists() {
            return isset($_SESSION[ $this->name ]);
        }


        public function save($data) {
            $_SESSION[ $this->name ] = $data;
        }


        public function clear() {
            $_SESSION[ $this->name ] = "";
            unset($_SESSION[ $this->name ]);

        }
    }