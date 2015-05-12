<?php

class FaPostLike {

    public function __construct(){

        add_action('wp_enqueue_scripts', array($this, 'load_scripts'),100);
        add_action( 'wp_ajax_nopriv_postlike', array($this, 'fa_ajax_post_action_callback'));
        add_action( 'wp_ajax_postlike', array($this, 'fa_ajax_post_action_callback'));
        add_filter('the_content',array($this,'fa_post_like'));

    }

    public function load_scripts(){
        
        wp_enqueue_style('wpl-css', WPL_URL . "/static/css/style.css", array(), WPL_VERSION, 'screen');
        wp_enqueue_script( 'wpl-js', WPL_URL . '/static/js/index.js', array(), WPL_VERSION, 'screen');
        wp_localize_script( 'wpl-js', 'wpl', array(
                                                        "ajax_url" =>  admin_url() . "admin-ajax.php"
                                                    ));
    }
    public function fa_ajax_post_action_callback(){
        global $post;
        $id = $_POST["id"];
        $action = $_POST["actionname"];

        if( $this->fa_is_post_liked($id) ) {
            echo json_encode(array('status'=>500,'data'=>'done!'));
            die;
        }
        if ( $action == 'ding'){

            $this->fa_update_postlike($id);

        }  else {

            $this->fa_update_postlike($id,true);

        }

        echo json_encode( array('status' => 200, 'data'=>array('like'=>$this->fa_get_post_like_num($id),
            'dislike'=>$this->fa_get_post_dislike_num($id)) ));

        die;
    }
    private function fa_get_post_like_num($id){
        $id = $id ? $id : get_the_ID();
        $num = get_post_meta($id,'_postlikes',true) ? get_post_meta($id,'_postlikes',true) : 0;
        return $num;
    }

    private function fa_get_post_dislike_num($id){
        $id = $id ? $id : get_the_ID();
        $num = get_post_meta($id,'_postdislikes',true) ? get_post_meta($id,'_postdislikes',true) : 0;
        return $num;
    }

    private function fa_is_post_liked($id){
        $id = $id ? $id : get_the_ID();
        if( isset($_COOKIE['post_action_'.$id])) {
            return true;
        } else {
            return false;
        }
    }

    public function fa_post_action_button($id){
        $id = $id ? $id : get_the_ID();
        $cookie = $this->fa_is_post_liked($id) ? $_COOKIE['post_action_'.$id] : '';
        if( $cookie ) {
            if( $cookie == 'ding'){
                $output = '<span class="action-item is-active">顶(' . $this->fa_get_post_like_num($id) . ')</span><span class="action-item">踩(' . $this->fa_get_post_dislike_num($id) . ')</span>';

            }else{
                $output = '<span class="action-item">顶(' . $this->fa_get_post_like_num($id) . ')</span><span class="action-item is-active">踩(' . $this->fa_get_post_dislike_num($id) . ')</span>';
            }

        } else {
            $output = '<span class="action-item" data-action="postlike" data-action-value="ding" data-id="' . $id . '">顶(' . $this->fa_get_post_like_num($id) . ')</span><span class="action-item" data-action="postlike" data-action-value="cai" data-id="' . $id . '">踩(' . $this->fa_get_post_dislike_num($id) . ')</span>';
        }
        return $output;
    }

    public function fa_post_like($content){
        global $post;
        $output = '<div id="post-action">' . $this->fa_post_action_button($post->ID) . '</div>';
        $content .= $output;
        return $content;
    }
    private function fa_update_postlike($id=null,$action=null){
        $id = $id ? $id : get_the_ID();
        $action = $action ? 'postdislike' : 'postlike';
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
        if($action == 'postlike'){
            $likenum = $this->fa_get_post_like_num($id) + 1;
            update_post_meta($id,'_postlikes',$likenum);
            setcookie('post_action_'.$id,'ding',$expire,'/',$domain,false);
        }else{
            $dislikenum = $this->fa_get_post_dislike_num($id) + 1;
            update_post_meta($id,'_postdislikes',$dislikenum);
            setcookie('post_action_'.$id,'cai',$expire,'/',$domain,false);
        }
    }
}
