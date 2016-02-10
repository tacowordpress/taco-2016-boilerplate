<?php

class TacoGatherContent {

    private $api_url;
    private $api_key;
    private $project_id;
    private $password;

    /**
     * Class Constructor
     * @return null
     */
    public function __construct($api_url, $api_key, $project_id)
    {
        $this->api_url = $api_url;
        $this->api_key = $api_key;;
        $this->password = 'x'; // leave it as 'x'
        $this->project_id = $project_id;
    }


    /**
     * Get Pages from Gather Content
     * @return array
     */
    public function getPages() {
        $data = $this->_curl(
            'get_pages_by_project',
            array('id' => $this->project_id)
        );

        return json_decode($data['response'])->pages;
    }


    /**
     * Save pages to WP keeping hierarchy
     * @param  $pages collection (array) of pages
     * @return null
     */
    public function savePagesToWP($pages, $save_content=false)
    {
        
        $pages = Taquito::arrayManipulate(function($k, $v)
            use ($save_content) {
            $id = $v->id;
            $post_content = null;
            if($save_content) {
                $config = json_decode(base64_decode($v->config))[0];
                $elements = $config->elements;
                // find anything pertaining to body copy/post_content
                foreach($elements as $e) {
                    if(preg_match('/body|content/i', $e->label)) {
                        $post_content = $e->value;
                    }
                }
            }
            return array(
                $id => array(
                    'name'  => $v->name,
                    'parent_id' => $v->parent_id,
                    'post_content' => $post_content
                )
            );
        }, $pages);
       
        foreach($pages as $k => $page) {
            $temp_array = array(
                'post_title'    => wp_strip_all_tags($page['name']),
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page'
            );
            if($save_content && strlen($page['post_content'])) {
                $temp_array['post_content'] = $page['post_content'];
            }
            $id = wp_insert_post(
                $temp_array
            );
            $pages[$k]['post_id'] = $id;
        }

        foreach($pages as $k => $page) {
            if ($page['parent_id'] === 0) continue;
            wp_update_post(
                array(
                    'ID' => $page['post_id'],
                    'post_parent' => $pages[$page['parent_id']]['post_id']
                )
            );
        }
    }


    /**
     * Function _curl
     * Using cURL to access GatherContent API
     * @param string
     * @param array
     * @return array
     */

    public function _curl($command = '', $postfields = array())
    {
        $postfields = http_build_query($postfields);
        $session = curl_init();

        curl_setopt($session, CURLOPT_URL, $this->api_url.$command);
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_USERPWD, $this->api_key . ":" . $this->password);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $postfields);

        if (substr($this->api_url, 0, 8) == 'https://') {
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($session);
        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
        curl_close($session);

        return array( 'code' => $httpcode, 'response' => $response );
    }
}