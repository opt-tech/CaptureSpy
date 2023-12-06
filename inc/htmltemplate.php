<?php


    /**
     * Created by PhpStorm.
     * User: kazuki kubota@castler
     * Date: 2016/07/22
     * Time: 3:21
     *
     * Ver 3.0  2016/07/22  PHP5で動作しないコードを修正
     */

     /* 利用方法 =====================================================================

     <form>
       <select name=foo>
          <option value="1" <!--{switch foo:1}-->selected<!--{/switch}--> >
          <option value="2" <!--{switch foo:2}-->selected<!--{/switch}--> >
          <option value="3" <!--{switch foo:3}-->selected<!--{/switch}--> >
        </select>
      </form>

      <form>
        <select name=foo>
          <option value="1" {switch foo:1:selected} >
          <option value="2" {switch foo:2:selected} >
          <option value="3" {switch foo:3:selected} >
        </select>
      </form>

        {iif hoge:1:trueprint:falseprint}

        <!--{def category/isFirat}-->
        hogehoge
        <!--{else}-->
        foo
        <!--{/def}-->


        <!--{each category}-->
        <tr id="category_base">
            <td>
                <select name="category[]" class="form-control category_combo">{val category/category}</select>
            </td>
        </tr>
        <!--{/each}-->

        {number 123456}	=>	123,456


    */


    interface ShouldClose {
    }


    /* the origine class of all tags */


    abstract class TagBasis {
        protected $matchregexp;
        protected $fromstring;
        protected $tostring;
        protected $closestring;


        public function parse($str, $multilabels) {

            while (preg_match($this->matchregexp, $str, $match)) {
                $m = "";
                $mt = "";

                if (count($match) == 2) {
                    $m = $match[1];
                    $ind = $this->getIndex($m, $multilabels);

                    $str = str_replace(sprintf($this->fromstring, $m), sprintf($this->tostring, $ind, $m), $str);
                } else {
                    for ($i = 1; $i < count($match); $i++) {
                        $mt[] = $m[] = $match[ $i ];
                    }

                    $mt[0] = $this->getIndex($mt[0], $multilabels);

                    $str = str_replace(vsprintf($this->fromstring, $m), vsprintf($this->tostring, $mt), $str);
                }
            }

            $str = $this->closeTag($str);

            return $str;
        }


        private function closeTag($str) {
            if ($this instanceof ShouldClose) {
                $str = str_replace($this->closestring, "<?php
				}
			?>", $str);
            }

            return $str;
        }


        abstract protected function getIndex($m, $multilabels);
    }


    /* the super class of tags which handle non-array data  */


    class SimpleTag extends TagBasis {
        protected function getIndex($m, $multilabels) {
            $ar = explode("/", $m);
            $ind = "";
            $rui = [];
            foreach ($ar as $x) {
                array_push($rui, $x);
                $ind .= "[\"$x\"]";
                if (in_array(join("/", $rui), $multilabels)) {
                    $ind .= "[\$cnt[\"" . join("/", $rui) . "\"]]";
                }
            }

            return $ind;
        }
    }


    /* the super class of tags which handle array structure
     like {each *}  */


    class MultiTag extends TagBasis {
        public function getLabelArray($str) {
            $ans = [];
            preg_match_all($this->matchregexp, $str, $regans, PREG_SET_ORDER);
            foreach ($regans as $x) {
                $ans[] = $x[1];
            }

            return $ans;
        }


        protected function getIndex($m, $multilabels) {
            $ar = explode("/", $m);
            $ind = "";
            $rui = [];
            $mattan = 0;
            foreach ($ar as $x) {
                array_push($rui, $x);
                $ind .= "[\"$x\"]";
                if ($mattan != count($ar) - 1 && in_array(join("/", $rui), $multilabels)) {
                    $ind .= "[\$cnt[\"" . join("/", $rui) . "\"]]";
                }
                $mattan++;
            }

            return $ind;
        }
    }


    /*
    *  parser classes
    */


    /* main definition of parser */


    class TemplateParser {
        private $tags = [
            "simple" => [],
            "multi"  => [],
        ];


        function add(TagBasis $tag) {
            if ($tag instanceof SimpleTag) $this->tags["simple"][] = $tag; elseif ($tag instanceof MultiTag) $this->tags["multi"][] = $tag;
            else throw new Exception("Tag class is not well defined.");

            return $this;
        }


        function parse($str) {
            reset($this->tags["multi"]);
            $multilabels = [];
            foreach ($this->tags["multi"] as $x) {
                $multilabels = array_merge($multilabels, $x->getLabelArray($str));
            }

            reset($this->tags["multi"]);
            foreach ($this->tags["multi"] as $x) {
                $str = $x->parse($str, $multilabels);
            }

            reset($this->tags["simple"]);
            foreach ($this->tags["simple"] as $x) {
                $str = $x->parse($str, $multilabels);
            }

            return $str;
        }
    }


    ////////////////////////////////////////////////////


    /*
    *   Standard tag classes
    *   these tags are defined as previous version of htmltemplate
    */


    class tag_val extends SimpleTag {
        protected $matchregexp = '/\{val ([^\}]+)\}/i';
        protected $fromstring  = "{val %s}";
        protected $tostring    = "<?php print nl2br(\$val%s); ?>\n";
    }


    class tag_rval extends SimpleTag {
        protected $matchregexp = '/\{rval ([^\}]+)\}/i';
        protected $fromstring  = "{rval %s}";
        protected $tostring    = "<?php print \$val%s; ?>\n";

    }


    class tag_def extends SimpleTag implements ShouldClose {
        protected $matchregexp = '/<!--\{def ([^\}]+)\}-->/i';
        protected $fromstring  = "<!--{def %s}-->";
        protected $tostring    = "<?php
		if((gettype(\$val%1\$s)!='array' && \$val%1\$s!=\"\") or (gettype(\$val%1\$s)=='array' && count(\$val%1\$s)>0)){ ?>";
        protected $closestring = "<!--{/def}-->";
    }


    class tag_each extends MultiTag implements ShouldClose {
        protected $matchregexp = '/<!--\{each ([^\}]+)\}-->/i';
        protected $fromstring  = "<!--{each %s}-->";
        protected $tostring    = "<?php
			for(\$cnt[\"%2\$s\"]=0;\$cnt[\"%2\$s\"]<count(\$val%1\$s);\$cnt[\"%2\$s\"]++){
				?>";
        protected $closestring = "<!--{/each}-->";
    }


    class tag_else extends SimpleTag {
        var $matchregexp = '/<!--\{else\}-->/i';
        var $fromstring  = "<!--{else}-->";
        var $tostring    = "<?php } else { ?>";
    }


    /*   <!--{switch hoge:val}-->～<!--{/switch}-->
    *    hogeがvalに等しい場合に、タグの間の～部分を表示する。
    */


    class tag_switch extends SimpleTag implements ShouldClose {
        var $matchregexp = '/<!--\{switch ([^\}:]+):([^\}:]+)\}-->/i';
        var $fromstring  = "<!--{switch %1\$s:%2\$s}-->";

        var $tostring = "<?php if(\$val%1\$s=='%2\$s'){ ?>";

        var $closestring = "<!--{/switch}-->";
    }


    /*   {switch hoge:val:～}
    *   hogeがvalに等しい場合に、タグ内の～部分を表示する。(単タグ)
    */


    class tag_switch_single extends SimpleTag {
        var $matchregexp = '/\{switch ([^\}:]+):([^\}:]+):([^\}:]+)\}/i';
        var $fromstring  = "{switch %1\$s:%2\$s:%3\$s}";

        var $tostring = "<?php if(\$val%1\$s=='%2\$s'){ @print '%3\$s'; } ?>";
    }


    class tag_iif extends SimpleTag {
        var $matchregexp = '/\{iif ([^\}:]+):([^\}:]+):([^\}:]+):([^\}:]+)\}/i';
        var $fromstring  = "{iif %1\$s:%2\$s:%3\$s:%4\$s}";

        var $tostring = "<?php if(\$val%1\$s=='%2\$s'){ @print '%3\$s'; } else { @print '%4\$s'; } ?>";
    }


    class tag_set_comma extends SimpleTag {
        var $matchregexp = '/\{number ([^\}]+)\}/i';
        var $fromstring  = "{number %s}";

        var $tostring = "<?php @print number_format(\$val%1\$s); ?>\n";
    }


    class tag_h extends SimpleTag {
        protected $matchregexp = '/\{h ([^\}]+)\}/i';
        protected $fromstring  = "{h %s}";
        protected $tostring    = "<?php print htmlspecialchars(\$val%s, ENT_QUOTES, 'UTF-8'); ?>\n";
    }


    class tag_rh extends SimpleTag {
        protected $matchregexp = '/\{rh ([^\}]+)\}/i';
        protected $fromstring  = "{rh %s}";
        protected $tostring    = "<?php print nl2br(htmlspecialchars(\$val%s, ENT_QUOTES, 'UTF-8')); ?>\n";

    }


    /*
    *   StandardParser
    *   parser defined with above tags.
    *   behave as previous htmltemplate
    */


    class StandardParser extends TemplateParser {
        function StandardParser() {
            $this->add(new tag_val())->add(new tag_rval())->add(new tag_def())->add(new tag_each());

            $this->add(new tag_switch());
            $this->add(new tag_switch_single());
            $this->add(new tag_set_comma());

            $this->add(new tag_iif());
            $this->add(new tag_else());

            $this->add(new tag_h());
            $this->add(new tag_rh());
        }
    }


    /*
    *  htmltemplate
    *  the APIs defined after the manner of htmltemplate for PHP4
    *  tmp file generation has not been implemented yet.(2003-07-08)
    */


    class htmltemplate {
        private        $parser;
        static private $instance;


        private function htmltemplate() {
            $this->parser = new StandardParser();
        }


        static public function getInstance() {
            if (!htmltemplate::$instance) htmltemplate::$instance = new htmltemplate();

            return htmltemplate::$instance;
        }


        static public function parse($str) {
            return htmltemplate::getInstance()->parser->parse($str);
        }


        static function t_include($file, $data) {
            print htmltemplate::t_buffer($file, $data);
        }

        // ↓↓↓ PHP5で動かないコード
        /*	static function t_buffer($file,$data){
                $val=$data;
                $all=fread(fopen($file,"rb"),filesize($file));
                $code=htmltemplate::parse($all);

                return eval('?>' .$code);
            }*/
        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


        static function t_buffer($file, &$data) {
            $val =& $data;
            $inst = htmltemplate::getInstance();
            $handle = fopen($file, "rb");
            $all = fread(fopen($file, "rb"), filesize($file));

            return $inst->buffer($all, $val);
        }


        function buffer($template_str, &$val) {
            $inst = htmltemplate::getInstance();
            $code = $inst->parse($template_str);
            ob_start();
            eval('?>' . $code);
            $cnt = ob_get_contents();
            ob_end_clean();

            return $cnt;
        }

    }