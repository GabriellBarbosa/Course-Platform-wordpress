<?php
class Course {
    private $repository;
    private $slug;
    private $user;

    public function __construct($courseSlug, CourseRepository $repository, User $user) {
        $this->repository = $repository;
        $this->slug = $courseSlug;
        $this->user = $user;
    }

    public function getContent() {
        $course = $this->repository->getCourse($this->slug);
        if ($course) {
            return array(
                'name' => $course['name'],
                'slug' => $course['slug'],
                'modules' => $this->getModules()
            );
        } else return null;
    }

    private function getModules() {
        $modules = $this->repository->getModules($this->slug);
        $result = array();
        foreach ($modules as $module) {
            array_push($result, array(
                'name' => $module->name,
                'sequence' => $module->description,
                'lessons' => $this->getLessons($module->slug)
            ));
        }
        return $result;
    }

    private function getLessons($moduleSlug) {
        $lessons = $this->repository->getLessons($this->slug, $moduleSlug);
        return $this->addCompletedLessonField($lessons);
    }

    private function addCompletedLessonField($lessons) {
        $result = array();
        foreach ($lessons as $lesson) {
            $lessonCopy = $lesson;
            $lessonCopy['completed'] = $this->lessonIsCompleted($lessonCopy['slug']);
            array_push($result, $lessonCopy);
        }
        return $result;
    }

    private function lessonIsCompleted($lessonSlug) {
        $completedLessons = $this->repository->getCompletedLessons($this->slug);
        foreach ($completedLessons as $completed) {
            if ($completed->lessonSlug == $lessonSlug) 
                return true;
        }
        return false;
    }

    public function findLesson($lessonSlug) {
        $rawLesson = $this->repository->getSingleLesson($this->slug, $lessonSlug);
        if ($rawLesson != null) {
            $lesson = $this->createLesson($rawLesson);
            return $lesson->getData();
        }
        return null;
    }

    private function createLesson($rawLesson) {
        if ($this->user->isSubscribed() ) {
            return new LessonForSubscribed(
                $rawLesson, $this->repository->getCompletedLessons($this->slug));
        } else if ($rawLesson['free'] == 'true') {
            return new LessonFree($rawLesson);
        } else {
            return new LessonForUnsubscribed($rawLesson);
        }
    }
    
    public function completeLesson($lessonSlug, $user) {
        if ($user->isSubscribed()) {
            $isCompleted = $this->repository->completeLesson($this->slug, $lessonSlug);
            return $isCompleted;
        }
        throw new Exception('Não foi possível completar a aula');
    }
}
?>