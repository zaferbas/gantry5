<?php
/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   Dual License: MIT or GNU/GPLv2 and later
 *
 * http://opensource.org/licenses/MIT
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Gantry Framework code that extends GPL code is considered GNU/GPLv2 and later
 */

namespace Gantry\Component\Translator;

use Gantry\Component\File\CompiledYamlFile;

class Translator implements TranslatorInterface
{
    protected $default = 'en';
    protected $active = 'en';
    protected $sections = [];
    protected $translations = [];

    public function translate($string)
    {
        list($domain, $section, $code) = explode('_', $string, 3);

        if ($domain === 'GANTRY5') {
            $translation = ($this->find($this->active, $section, $string) ?: $this->find($this->default, $section, $string)) ?: $code;
        } else {
            $translation = $string;
        }

        return $translation;
    }

    /**
     * Set new active language if given and return previous active language.
     *
     * @param  string  $language  Language code. If not given, current language is kept.
     * @return string  Previously active language.
     */
    public function active($language = null)
    {
        $previous = $this->active;

        if ($language) {
            $this->active = $language;
        }

        return $previous;
    }

    protected function find($language, $section, $string)
    {
        if (!isset($this->sections[$language][$section])) {
            $translations = $this->load($language, $section);

            if (isset($this->translations[$language])) {
                $this->translations[$language] += $translations;
            } else {
                $this->translations[$language] = $translations;
            }

            $this->sections[$language][$section] = !empty($translations);
        }

        return isset($this->translations[$language][$string]) ? $this->translations[$language][$string] : null;
    }

    protected function load($language, $section)
    {
        $section = strtolower($section);
        $filename = 'gantry-admin://translations/' . $language . '/' . $section . '.yaml';
        $file = CompiledYamlFile::instance($filename);

        if (!$file->exists() && ($pos = strpos($language, '-')) > 0) {
            $filename = 'gantry-admin://translations/' . substr($language, 0, $pos) . '/' . $section . '.yaml';
            $file = CompiledYamlFile::instance($filename);
        }

        $translations = (array) $file->content();
        $file->free();

        return $translations;
    }
}
