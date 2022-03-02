<?php 

require __DIR__ . '/vendor/autoload.php';

use Mateodioev\Translate;

$tr = new Translate;

// $tr->google('Hello world', 'en', 'es');

$res = $tr->setText('Hello world')
   ->setInputLang('en')
   ->setOutputLang('es')
   ->google();

var_dump($res);

/*
  Output:
    class stdClass#5 (2) {
      public $input =>
      class stdClass#2 (3) {
        public $lang_code =>
        string(2) "en"
        public $lang_name =>
        string(7) "English"
        public $text =>
        string(11) "Hello world"
      }
      public $output =>
      class stdClass#4 (3) {
        public $lang_code =>
        string(2) "es"
        public $lang_name =>
        string(7) "Spanish"
        public $text =>
        string(10) "Hola Mundo"
      }
    }
*/