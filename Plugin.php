<?php
/**
 * 微信公众号涨粉插件，支持个人公众号自定义底部菜单，强制动态密码。
 * @package WechatFans
 * @author  杨柳清风<br>二呆(原作者)
 * @version 1.0.0
 * @link http://blog.ylwind.cn/
 * @date 2021-09-24
 */
date_default_timezone_set('Asia/Shanghai');
require(__DIR__ . DIRECTORY_SEPARATOR . 'Core.php');
class WechatFans_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
        $db = Typecho_Db::get();
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('WechatFans_Plugin', 'tleWechatFansToolbar');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('WechatFans_Plugin', 'tleWechatFansToolbar');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('WechatFans_Plugin', 'contentEx');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('WechatFans_Plugin', 'excerptEx');
        return _t('插件已经激活，需先配置二维码图片信息！');
    }

    // 禁用插件
    public static function deactivate(){
        $db = Typecho_Db::get();
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
        $core=new Core();
        $options = Typecho_Widget::widget('Widget_Options');
        $plug_url = $options->pluginUrl;
        $div=new Typecho_Widget_Helper_Layout();
        $div->html('
			<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<h6>使用方法</h6>
			<span><p>1、配置下方各项参数；</p></span>
			<span><p>2、编写文章时点击编辑器VX按钮，插入以下代码：<br />&lt;!--wechatfans start-->xxx&lt;!--wechatfans end--><br />代码中间的文字xxx即为隐藏内容；</p></span>
			<span>
				<p>
					3、<font color="blue">【替换输出内容】</font><br />
					替换主题目录下post.php中输出内容的代码，<font color="red">若已有自定义的输出内容代码，则可以不替换</font>，如：<br />
					&lt;?php $this->content; ?>替换成&lt;?php echo $this->content; ?>
				</p>
			</span>
						<span>
				<p>
					4、<font color="blue">【微信公众号配置】</font><br />
					登录微信公众号后台，点击侧边栏广告与服务-小程序管理，关联小程序<font color="red">杨柳验证码</font>，然后点击内容与互动-自定义菜单，添加一个菜单跳转到“杨柳验证码”小程序，路径填写为 <font color="red">生成密钥时显示的小程序路径</font>
				</p>
			</span>
		</small>');
        $div->render();

        $wechat_name = new Typecho_Widget_Helper_Form_Element_Text('wechat_name', array("value"), '杨柳清风博客', _t('微信公众号名称'), _t('微信公众号平台→公众号设置→名称，例如：杨柳清风博客'));
        $form->addInput($wechat_name);
        $wechat_account = new Typecho_Widget_Helper_Form_Element_Text('wechat_account', array("value"), 'ylwind-cn', _t('微信公众号账号'), _t(' 微信公众号平台→公众号设置→微信号，例如：ylwind-cn'));
        $form->addInput($wechat_account);
        $wechat_qrimg = new Typecho_Widget_Helper_Form_Element_Text('wechat_qrimg', array("value"), 'https://z3.ax1x.com/2021/09/24/4BkxNd.jpg', _t('微信公众号二维码地址'), _t('填写您的微信公众号的二维码图片地址，建议150X150像素'));
        $form->addInput($wechat_qrimg);
        $wechat_day = new Typecho_Widget_Helper_Form_Element_Text('wechat_day', array("value"), '30', _t('Cookie有效期天数'), _t('在有效期内，访客无需再获取验证码可直接访问隐藏内容'));
        $form->addInput($wechat_day);
        $wechat_key = new Typecho_Widget_Helper_Form_Element_Text('wechat_key', array("value"), '', _t('加密密钥(务必使用生成接口生成)'), _t('用于加密Cookie，生成验证码，<a href="https://v-code.app.ylwind.cn/creat" target="_blank">点击这里生成密钥</a>'));
        $form->addInput($wechat_key);

    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('WechatFans');
    }

    /**
     * 后台编辑器添加微信公众号涨粉按钮
     * @access public
     * @return void
     */
    public static function tleWechatFansToolbar(){
        ?>
        <script type="text/javascript">
            $(function(){
                if($('#wmd-button-row').length>0){
                    $('#wmd-button-row').append('<li class="wmd-button" id="wmd-button-wechatfans" style="font-size:14px;float:left;color:#AAA;width:20px;" title=微信公众号涨粉><b>VX</b></li>');
                }else{
                    $('#text').before('<a href="#" id="wmd-button-wechatfans" title="微信公众号涨粉"><b>VX</b></a>');
                }
                $(document).on('click', '#wmd-button-wechatfans', function(){
                    $('#text').val($('#text').val()+'\r\n<!--wechatfans start-->\r\n\r\n<!--wechatfans end-->');
                });
                /*移除弹窗*/
                if(($('.wmd-prompt-dialog').length != 0) && e.keyCode == '27') {
                    cancelAlert();
                }
            });
            function cancelAlert() {
                $('.wmd-prompt-dialog').remove()
            }
        </script>
        <?php
    }

    /**
     * 自动输出摘要
     * @access public
     * @return void
     */
    public static function excerptEx($html, $widget, $lastResult){
        $wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
        preg_match_all($wechatfansRule, $html, $hide_words);
        if(!$hide_words[0]){
            $wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
        }
        $WeMediaRule='/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i';
        preg_match_all($WeMediaRule, $html, $hide_words);
        if(!$hide_words[0]){
            $WeMediaRule='/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i';
        }
        $html=trim($html);
        if (preg_match_all($wechatfansRule, $html, $hide_words)){
            $html = str_replace($hide_words[0], '', $html);
        }
        if (preg_match_all($WeMediaRule, $html, $hide_words)){
            $html = str_replace($hide_words[0], '', $html);
        }
        $html=Typecho_Common::subStr(strip_tags($html), 0, 140, "...");
        return $html;
    }

    /**
     * 自动输出内容
     * @access public
     * @return void
     */
    public static function contentEx($html, $widget, $lastResult){
        $core = new Core();
        $wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
        preg_match_all($wechatfansRule, $html, $hide_words);
        if(!$hide_words[0]){
            $wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
        }
        $html = empty( $lastResult ) ? $html : $lastResult;
        $option=self::getConfig();
        $cookie_name = 'wechat_fans_post_'.md5($html);
        $html=trim($html);
        if (preg_match_all($wechatfansRule, $html, $hide_words)){
            $cv = md5($option->wechat_key.$cookie_name);
            $vtips='';
            if(isset($_POST['wxfans_verifycode'])){
                if($core->verifyCode($option->wechat_key,$_POST['wxfans_verifycode'],5)){
                    setcookie($cookie_name, $cv ,time()+(int)$option->wechat_day*86400, "/");
                    $_COOKIE[$cookie_name] = $cv;
                }else{
                    $vtips='<script>alert("验证码错误！请输入正确的验证码！");</script>';
                }
            }
            $cookievalue = isset($_COOKIE[$cookie_name])?$_COOKIE[$cookie_name]:'';

            if($cookievalue==$cv){
                $html = str_replace($hide_words[0], '<div style="border:1px dashed #0396ff; border-radius:var(--radius-inner); padding:10px; margin:10px 0; line-height:200%; overflow:hidden; clear:both;"><p style="color:red;">隐藏内容</p>'.$hide_words[0][0].'</div>', $html);
                $html = str_replace("&lt;","<", $html);
                $html = str_replace("&gt;",">", $html);
            }else{
                $hide_notice = '<div class="huoduan_hide_box" style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;"><img class="wxpic" align="right" src="'.$option->wechat_qrimg.'" style="width:150px;height:150px;margin-left:20px;display:inline;border:none" width="150" height="150"  alt="'.$wechatfans['wechat_name'].'" /><span style="font-size:18px;">此处内容已经被作者隐藏，请输入验证码查看内容</span><form method="post" style="margin:10px 0;"><span class="yzts" style="font-size:18px;float:left;">验证码：</span><input name="wxfans_verifycode" id="verifycode" type="text" value="" style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" /><input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="提交查看" /></form><div style="clear:left;"></div><span style="color:#00BF30"><br>请关注本站微信公众号即可获取验证码。在微信里搜索“<span style="color:blue">'.$option->wechat_name.'</span>”或者微信扫描右侧二维码即可关注本站微信公众号。</span><div class="cl"></div></div>'.$vtips;
                $html = str_replace($hide_words[0], $hide_notice, $html);
            }
        }
        return $html;
    }
}
