<?php 

namespace Mateodioev;

use Mateodioev\Request\Request;

use Exception;

/**
 * Translate texts
 * 
 * @method google
 */
class Translate {
  
  const GOOGLE_ENDPOINT = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=%s&tl=%s&dt=t&q=%s';
  private $langs_code = ['auto' => 'Automatic', 'af' => 'Afrikaans', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'hy' => 'Armenian', 'az' => 'Azerbaijani', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali', 'bs' => 'Bosnian', 'bg' => 'Bulgarian', 'ca' => 'Catalan', 'ceb' => 'Cebuano', 'ny' => 'Chichewa', 'zh-cn' => 'Chinese Simplified', 'zh-tw' => 'Chinese Traditional', 'co' => 'Corsican', 'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'tl' => 'Filipino', 'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Frisian', 'gl' => 'Galician', 'ka' => 'Georgian', 'de' => 'German', 'el' => 'Greek', 'gu' => 'Gujarati', 'ht' => 'Haitian Creole', 'ha' => 'Hausa', 'haw' => 'Hawaiian', 'iw' => 'Hebrew', 'hi' => 'Hindi', 'hmn' => 'Hmong', 'hu' => 'Hungarian', 'is' => 'Icelandic', 'ig' => 'Igbo', 'id' => 'Indonesian', 'ga' => 'Irish', 'it' => 'Italian', 'ja' => 'Japanese', 'jw' => 'Javanese', 'kn' => 'Kannada', 'kk' => 'Kazakh', 'km' => 'Khmer', 'ko' => 'Korean', 'ku' => 'Kurdish (Kurmanji)', 'ky' => 'Kyrgyz', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian', 'lt' => 'Lithuanian', 'lb' => 'Luxembourgish', 'mk' => 'Macedonian', 'mg' => 'Malagasy', 'ms' => 'Malay', 'ml' => 'Malayalam', 'mt' => 'Maltese', 'mi' => 'Maori', 'mr' => 'Marathi', 'mn' => 'Mongolian', 'my' => 'Myanmar (Burmese)', 'ne' => 'Nepali', 'no' => 'Norwegian', 'ps' => 'Pashto', 'fa' => 'Persian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ma' => 'Punjabi', 'ro' => 'Romanian', 'ru' => 'Russian', 'sm' => 'Samoan', 'gd' => 'Scots Gaelic', 'sr' => 'Serbian', 'st' => 'Sesotho', 'sn' => 'Shona', 'sd' => 'Sindhi', 'si' => 'Sinhala', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'so' => 'Somali', 'es' => 'Spanish', 'su' => 'Sundanese', 'sw' => 'Swahili', 'sv' => 'Swedish', 'tg' => 'Tajik', 'ta' => 'Tamil', 'te' => 'Telugu', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek', 'vi' => 'Vietnamese', 'cy' => 'Welsh', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'zu' => 'Zulu'];

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
    $this->input->lang_name = $this->langs_code['auto'];
    $this->output->lang_code = 'en';
    $this->output->lang_name = $this->langs_code['en'];
  }

  /**
   * Set input lang code
   *
   * @param string $lang_code Language code, default is 'auto'
   * @throws Exception
   */
  public function setInputLang(string $lang_code = 'auto')
  {
    if (isset($this->langs_code[$lang_code])) {
      $this->input->lang_code = $lang_code;
      $this->input->lang_name = $this->langs_code[$lang_code];
      return $this;
    } else {
      throw new Exception("Invalid input language code");
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
    if (isset($this->langs_code[$lang_code])) {
      $this->output->lang_code = $lang_code;
      $this->output->lang_name = $this->langs_code[$lang_code];
      return $this;
    } else {
      throw new Exception("Invalid output language code");
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

  private function getUrl(string $url, string $input_text=null, string $source=null, string $target=null)
  {
    $this->input->text = $input_text ?? $this->input->text;
    $this->input->lang_code  = $source ?? $this->input->lang_code;
    $this->output->lang_code = $target ?? $this->output->lang_code;
    return sprintf($url, urlencode($this->input->lang_code), urlencode($this->output->lang_code), urlencode($this->input->text));
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
    if ($input_text) $this->setText($input_text);
    if ($source) $this->setInputLang($source);
    if ($target) $this->setOutputLang($target);
    

    if (!$this->Eval()) return false;
    
    $response = Request::get($this->getUrl(self::GOOGLE_ENDPOINT, $input_text, $source, $target));

    if (!$response['ok'] || empty($response['response'])) {
      $this->error = true;
      $this->error_msg = 'Error: ' . $response['error'];
      return false;
    }
    $res = json_decode($response['response']);
    $lines = count($res[0]);
    $content = '';

    for ($i = 0; $i < $lines; $i++) {
      $content .= $res[0][$i][0];
    }

    $this->input->lang_code = $res[2];
    $this->input->lang_name = $this->langs_code[$this->input->lang_code];

    if ($this->input->lang_code == $this->output->lang_code) {
      $this->error = true;
      $this->error_msg = 'The text is already in the language you want to translate to ('.$this->input->lang_name.')';
      return false;
    }
    $this->output->text = $content;
    $this->output->lang_name = $this->langs_code[$this->output->lang_code];
    return (object) [
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

}