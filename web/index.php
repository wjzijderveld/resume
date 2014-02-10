<?php
/**
 * Created at 01/02/14 11:35
 */

require '../bootstrap.php';
use Symfony\Component\Yaml;

$logger = new \Monolog\Logger('general', array(
    new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/errors.log'),
));
$errorHandler = new \Monolog\ErrorHandler($logger);
$errorHandler->registerErrorHandler(array(), false);
$errorHandler->registerExceptionHandler(null, false);
$errorHandler->registerFatalHandler();

$templateLoader = new Twig_Loader_Filesystem(array(__DIR__ . '/../templates'));
$twig = new Twig_Environment($templateLoader);
$twig->addFilter(new Twig_SimpleFilter('paragraph', function($text) {
    return '<p>' . preg_replace('/\n(\s*\n)/', '</p><p>', $text) . '</p>';
}, array('is_safe' => array('html'))));

$jobs = $skills = $educations = $settings = array();
try {
    $jobs = Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../resources/jobs.yml'));
} catch (Yaml\Exception\ParseException $e) {
    if ($logger instanceOf \Psr\Log\LoggerInterface) {
        $logger->critical('Failed to parse the jobs.yml', array($e->getMessage()));
    }
}

try {
    $skills = Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../resources/skills.yml'));
} catch (Yaml\Exception\ParseException $e) {
    if ($logger instanceOf \Psr\Log\LoggerInterface) {
        $logger->critical('Failed to parse the skills.yml', array($e->getMessage()));
    }
}

try {
    $educations = Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../resources/education.yml'));
} catch (Yaml\Exception\ParseException $e) {
    if ($logger instanceOf \Psr\Log\LoggerInterface) {
        $logger->critical('Failed to parse the education.yml', array($e->getMessage()));
    }
}

try {
    $settings = Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../config/settings.yml'));
} catch (\Yaml\Exception\ParseException $e) {
    if ($logger instanceOf \Psr\Log\LoggerInterface) {
        $logger->critical('Failed to parse the settings.yml', array($e->getMessage()));
    }
}

try {
    echo $twig->render('layout.html.twig', array(
        'jobs'          => $jobs,
        'skills'        => $skills,
        'educations'    => $educations,
        'showAnalytics' => $settings['show_analytics'],
    ));
} catch (\Exception $e) {
    echo <<<HTML
<html>
<head>
<link rel="stylesheet" href="assets/css/vendor.css" />
<link rel="stylesheet" href="assets/css/screen.css" />
</head>
<body>
    <div class="row">
        <div class="small-12 columns">
            <h2>I'm so sorry, I screwed up pretty badly apparently...</h2>
        </div>
    </div>
</body>
</html>
HTML;

    if ($logger instanceof \Psr\Log\LoggerInterface) {
        $logger->critical('Failed to parse template', array($e->getMessage()));
    }
}
