## Typecho微信公众号涨粉插件1.0.0

---
一款利于微信公众号涨粉的Typecho插件


### 安装方法：

	第一步：下载本插件，放在 `usr/plugins/` 目录中（插件文件夹名必须为WechatFans）；
	第二步：激活插件；
	第三步：按照使用方法配置。
	
### 使用方法：

	1、配置各项参数；
	2、编写文章时点击编辑器VX按钮，插入以下代码：
	<!--wechatfans start-->xxx<!--wechatfans end-->
	代码中间的文字xxx即为隐藏内容；
	3、【替换输出内容！】
	替换主题目录下post.php中输出内容的代码，若已有自定义的输出内容代码，则可以不替换，如：<?php $this->content; ?>替换成<?php echo $this->content; ?>
	4、【微信公众号配置】
	登录微信公众号后台，点击侧边栏广告与服务-小程序管理，关联小程序[杨柳验证码]，然后点击内容与互动-自定义菜单，添加一个菜单跳转到[杨柳验证码]小程序，路径填写为 <font color="red">pages/index/index?id={你的加密密钥}
	
### 特点

	1、只需关注公众号，无需回复内容即可获得验证码
	2、个人号也可以使用，不影响原有的自定义菜单
	3、动态验证码，更安全更好用
	4、安全稳定无广告！

### 作者：
	作者：杨柳清风
	网站：https://blog.ylwind.cn
	公众号：杨柳清风博客

### 原作者：

	作者：二呆
	网站：http://www.tongleer.com/
	公众号：同乐儿
### 其他

#### Demo

[点击这里体验](https://blog.ylwind.cn/archives/26.html)

#### 开源许可

[GPL](https://www.gnu.org/licenses/gpl-3.0.en.html)
