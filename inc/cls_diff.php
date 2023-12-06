<?php
    ini_set("display_errors", 0);
    require_once(LIB_DIR . "Dom/simple_html_dom.php");
    require_once(LIB_DIR . 'Diff/TextDiff.php');


    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/10/07
     * Time: 16:17
     */
    class Diff {
        private $file1;
        private $file2;

        private $fileName1;
        private $fileName2;

        public $diff;


        /**
         * diff constructor.
         *
         * @param $file1
         * @param $file2
         */
        public function __construct($file1, $file2) {
            $this->file1 = $this->loadHtmlFile($file1);
            $this->file2 = $this->loadHtmlFile($file2);

            $this->diff = new TextDiff(htmlspecialchars($this->file1), htmlspecialchars($this->file2));
        }


        private function loadHtmlFile($html) {
            $html = Diff::extractTagBorn($html);
            $html = str_replace("><", ">\n<", $html);
            $html = str_replace("> <", ">\n<", $html);

            return $html;

        }

        //private function HtmlFormat($html) {
        //    //$html = str_replace(";", ";\n",$html);
        //    $html = $this->dom_repair_string($html);
        //    $html = tidy_repair_string($html, [
        //        'indent'               => TRUE,
        //        'drop-empty-paras'     => TRUE,
        //        'wrap-script-literals' => TRUE,
        //        'wrap-sections'        => TRUE,
        //        'wrap'                 => 0,
        //    ], 'utf8');
        //
        //
        //
        //    return $this->dom_repair_string($html);
        //    //$html = str_replace(";", ";\n", $html);
        //
        //    //return $html;
        //
        //}


        //private function dom_repair_string($html) {
        //    static $dom;
        //    static $conv;
        //    static $rev;
        //    if ($dom === NULL) {
        //        $dom = new DOMDocument;
        //        $conv = function ($html) {
        //            return mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        //        };
        //        $rev = function ($matches) {
        //            return mb_convert_encoding($matches[0], 'UTF-8', 'HTML-ENTITIES');
        //        };
        //    }
        //    @$dom->loadHTML($conv($html));
        //
        //    return preg_replace_callback('/(?:&#\d++;)++/', $rev, $dom->saveHTML());
        //}


        public function exec() {
            $ret = ["totalCount" => $this->getTotalCount()];
            $ret["diffCount"] = $this->getDiffCount();

            $ret["file1"] = $this->fileName1;
            $ret["file2"] = $this->fileName2;

            return $ret;
        }


        public static function extractTagBorn($html) {
            $dom = str_get_html($html);

            foreach ($dom->find('span') as &$element) $element->innertext = "";
            foreach ($dom->find('h2') as &$element) $element->innertext = "";
            foreach ($dom->find('h3') as &$element) $element->innertext = "";
            foreach ($dom->find('a') as &$element) $element->outertext = "";
            //foreach ($dom->find('a') as &$element) $element->innertext = "";
            foreach ($dom->find('cite') as &$element) $element->innertext = "";
            //foreach ($dom->find('div') as &$element) $element->innertext = "";
            //foreach ($dom->find('div') as &$element) print $element->simpletext;

            //var_dump($dom->find('.ads-creative'));

            /*$cnt = 0;
            foreach ($dom->find('.ellip') as &$element) {
                $cnt++;
               $elm = $element->first_child();
               //if ($elm->nodetype == HDOM_TYPE_TEXT) $elm->innertext = "";
                print $cnt." is ".$elm->nodetype." = ".$elm->innertext."\n<br>";

            }*/

            //print "start";
            //Diff::removeDiv($dom->find('div'));
            //print "end";

            return $dom;
        }

        public static function removeDiv(&$elements){
            print "removeDiv start \n<br>";

            foreach ($elements as &$element) {
                if(!empty($element)) {

                    if ($element->nodetype == HDOM_TYPE_TEXT) {
                        print "remove {$element}";
                        $element->innertext = "";
                    } else if ($element->nodetype) {
                        $elm = $element->first_child();
                        if ($elm->nodetype == HDOM_TYPE_TEXT) {
                            $elm->innertext = "";
                        } else if ($elm->nodetype == HDOM_TYPE_ELEMENT) {
                            Diff::removeDiv($element->find('div'));
                        }
                    }
                }
            }
        }


        public function getHtml() {
            return $this->diff->getHtml();
        }


        public function getDiffCount() {
            return $this->diff->diffCount;
        }


        public function getTotalCount() {
            return $this->diff->getTotalCount();
        }


        public function getDiffRatio() {
            return round(($this->getDiffCount() / $this->getTotalCount()) * 100, 2);
        }

    }