<?php
require_once get_template_directory() . '/src/entities/Course.php';

add_action('rest_api_init', 'registerGetCourseContentRoute');

function registerGetCourseContentRoute() {
    $slug = '(?P<slug>[-\w]+)';
    $apiRoute = '/curso' . '/' . $slug;
    register_rest_route('api', $apiRoute, array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'getCourseContent',
            'permission_callback' => '__return_true'
        )
    ));
}

function getCourseContent($request) {
    $course = new Course(
        $request['slug'], new CourseRepositoryImpl());
    $content = $course->getContent();
    $response = $content 
        ? $content 
        : getNotFoundErr('O curso não foi encontrado');
    return rest_ensure_response($response);
}
?>