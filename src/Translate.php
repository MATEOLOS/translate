<?php 

namespace Mateodioev;

use Mateodioev\Request\Request;
use Exception;
use Mateodioev\Utils\Exceptions\RequestException;

/**
 * Translate texts
 * 
 * @method google
 */
class Translate {
  
  const GOOGLE_ENDPOINT = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=%s&tl=%s&dt=t&q=%s';
  const YANDEX_ENDPOINT = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=%s&lang=%s&text=%s';

  public object $input;
  public object $output;
  public bool $error = false;
  public string $error_msg = '';

  /**
   * Default parameters
   */
  public function __construct()
  {
    $this->input = new \stdClass;
    $this->output = new \stdClass;
    $this->input->lang_code = 'auto';
    $this->input->lang_name = Langs::getName('auto');
    $this->output->lang_code = 'en';
    $this->output->lang_name = Langs::getName('en');
  }

  /**
   * Set input lang code
   *
   * @param string $lang_code Language code, default is 'auto'
   * @throws Exception
   */
  public function setInputLang(string $lang_code = 'auto')
  {
    $lang_code = strtolower($lang_code);

    if (Langs::getName($lang_code) !== false) {
      $this->input->lang_code = $lang_code;
      $this->input->lang_name = Langs::getName($lang_code);
      return $this;
    } else {
      throw new TranslateException('Invalid input language code: "'.$lang_code.'"');
    }
  }

  /**
   * Set output lang code
   *
   * @param string $lang_code Language code
   * @throws Exception
   */
  public function setOutputLang(string $lang_code = 'en')
  {
    $lang_code = strtolower($lang_code);

    if (Langs::getName($lang_code) !== false && $lang_code != 'auto') {
      $this->output->lang_code = $lang_code;
      $this->output->lang_name = Langs::getName($lang_code);
      return $this;
    } else {
      throw new TranslateException('Invalid output language code: "'.$lang_code.'"');
    }
  }

  /**
   * Set text to translate
   *
   * @param string $txt Any string
   */
  public function setText(string $txt)
  {
    $this->input->text = $txt;
    return $this;
  }

  private function ParseParams(string $input_text=null, string $source=null, string $target=null)
  {
    $this->setText($input_text ?? $this->input->text ?? '');
    $this->setInputLang($source ?? $this->input->lang_code ?? 'auto');
    $this->setOutputLang($target ?? $this->output->lang_code ?? 'en');
  }

  private function Eval(): bool
  {
    if (!isset($this->input->text) || $this->input->text == '') {
      $this->error = true;
      $this->error_msg = 'No text to translate';
      return false;
    }
    if ($this->input->lang_code == $this->output->lang_code) {
      $this->error = true;
      $this->error_msg = 'Input and output language are the same';
      return false;
    }
    return true;
  }

  /**
   * Translate text using Google Translate API
   *
   * @param string|null $input_text Text to translate
   * @param string|null $source Source language code
   * @param string|null $target Target language code
   */
  public function google(string $input_text=null, string $source=null, string $target=null)
  {
    $this->ParseParams($input_text, $source, $target);
    if (!$this->Eval()) return false;
    
    $url = sprintf(self::GOOGLE_ENDPOINT, urlencode($this->input->lang_code), urlencode($this->output->lang_code), urlencode($this->input->text));

    try {
      $res = Request::get($url, [CURLOPT_HTTPHEADER => ['Content-Type: application/json']])
      ->Run(null)->toJson(true);
    } catch (RequestException $e) {
      $this->error = true;
      $this->error_msg = $e->getMessage();
      return false;
    }

    if ($res->isError()) {
      $this->error = true;
      $this->error_msg = $res->getErrorMessage();
      return false;
    }
    $res = $res->getBody();
    $lines = count($res[0]);
    $content = '';

    for ($i = 0; $i < $lines; $i++) {
      $content .= $res[0][$i][0];
    }

    $this->input->lang_code = strtolower($res[2]);
    $this->input->lang_name = Langs::getName($this->input->lang_code);

    if ($this->input->lang_code == $this->output->lang_code) {
      $this->error = true;
      $this->error_msg = 'The text is already in the language you want to translate to ('.$this->input->lang_name.')';
      return false;
    }
    $this->output->text = $content;
    $this->output->lang_name = Langs::getName($this->output->lang_code);
    return (object) [
      'input' => $this->input,
      'output' => $this->output
    ];
  }

  /**
   * Yandex Translate API v1.5
   *
   * @param string $api_key https://translate.yandex.com/developers/keys
   * @param string|null $input_text Text to translate
   * @param string|null $source Source language code
   * @param string|null $target Target language code
   * 
   * @link https://yandex.com/dev/translate/doc/dg/reference/translate.html
   */
  public function yandex(string $api_key, string $input_text=null, string $source=null, string $target=null)
  {
    $this->ParseParams($input_text, $source, $target);
    if (!$this->Eval()) return false;

    if (empty($api_key)) {
      throw new Exception("Put your api key in the Yandex Translate API key");
    }

    $lang = $this->input->lang_code . '-' . $this->output->lang_code;
    if ($this->input->lang_code == 'auto') $lang = $this->output->lang_code;
    $url = sprintf(self::YANDEX_ENDPOINT, urlencode($api_key), urlencode($lang), urlencode($this->input->text));

    $res = (new Request)->init($url, [CURLOPT_HTTPHEADER => ['Content-Type: application/json']])
      ->Run(null)->toJson(true)
      ->getBody();

    if ($res->code != 200) {
      $this->error = true;
      $this->error_msg = 'Error:  '. $res->message;
      return false;
    }

    $langs = explode('-', $res->lang);
    $this->input->lang_code = strtolower($langs[0]);
    $this->input->lang_name = Langs::getName($this->input->lang_code);

    if ($langs[0] == $langs[1]) {
      $this->error = true;
      $this->error_msg = 'The text is already in the language you want to translate to ('.$this->input->lang_name.')';
      return false;
    }

    $this->output->text = $res->text[0];
    $this->output->lang_name = Langs::getName($this->input->lang_code);
    return(object) [
      'input' => $this->input, 
      'output' => $this->output
    ];
  }

  /**
   * Get text translated
   */
  public function getText(): string 
  {
    return $this->output->text ?? '';
  }

  /**
   * Get lang name
   *
   * @param string $dir input|output
   */
  public function getLangName(string $dir = 'output'): string
  {
    return $this->{$dir}->lang_name ?? '';
  }

  public function getError(): string
  {
    return $this->error_msg;
  }
}
