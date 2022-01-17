<div align="center">

<img src="https://try.geteso.org/assets/img/logo.svg" alt="eso" width="150"/><br>

**Forum software that's lightweight and extensible.**

<p align="justify"><b>eso is a web forum software</b> originally created for <a href="https://geteso.org">geteso, the unmoderated¹ discussion board</a> which brings a whole new approach to how users interact.&nbsp&nbspUsing our software, webmasters can <b>create a functional and speedy forum</b> in a short manner of time.&nbsp&nbspIt's a small package and loads <i>freaky fast</i> on modern browsers using compression and an unsophisticated design.

[![Code Size](https://img.shields.io/github/languages/code-size/geteso/eso?style=plastic)]()
[![Issues](https://img.shields.io/github/issues/geteso/eso?style=plastic)]()
[![License](https://img.shields.io/github/license/geteso/eso?style=plastic)]()
[![Version](https://img.shields.io/github/v/release/geteso/eso?include_prereleases&style=plastic)]()
[![PHP Version Support](https://img.shields.io/badge/php-%5E5.6.4-blue?style=plastic)]()

</div>

---

### About the project
Our software is based on an earlier incarnation of an unmaintained project named [*esoTalk*](https://github.com/esotalk/esoTalk) headed by <a href="http://tobyzerner.com/">Toby Zerner</a>.  We started a small, private forum by the name of "esotalk.ga" in order to test our fork of the software almost four years ago.  Fast forward to the present, this forum software now revolves around the forum itself, which has grown into a place for members to discuss what they please.

That doesn't mean it isn't great for individual forums, though.  Since then, we've added an administrator panel and are in the process of following a roadmap to better fulfill the needs of our users.  Changes are always being made as our users [make suggestions on the forum](https://geteso.org/1020/), something that we openly encourage.  We are not affiliated with the original *esoTalk* project.

### Creating your own forum
**It's very easy to create your own forum** and doesn't require much (if any) command-line experience.  **For an extremely novice tutorial, see [this tutorial](https://github.com/geteso/eso/wiki/The-beginner's-guide-to-creating-your-forum)**.  Otherwise, dependencies are listed below.

1. **[Apache](https://apache.org)** or [lighttpd](https://www.lighttpd.net) (not yet tested).
2. **[MySQL](https://www.mysql.com) 5.7 or greater** and an empty database.
2. **[PHP 5.3-5.6](https://www.php.net/releases/5_6_0.php)** as version seven is not yet supported.
4. **PHP-MySQL and PHP-GD** extensions.

**For a production forum, you should serve all traffic over HTTPS/TLS.**  This can easily be done by installing [LetsEncrypt](https://letsencrypt.org/).  Our software requires no additional configuration for HTTPS/TLS other than changing the URL in your `config.php` from `http://example.com` -> `https://example.com`.

### Getting support
The people who are responsible for heading development on this project also regularly check the discussion board that it is made for, which means that you'd be better off asking your questions [on our forum](https://geteso.org).  If you're uncomfortable with that, you can always [open an issue](https://github.com/geteso/eso/issues), however we cannot guarantee a response within any short manner of time.

As always, the best way to contribute is by joining our forum and discussing the project with us there.

---

¹The geteso forum is not entirely unmoderated.  See [our about page](https://geteso.org/about/2) for details.
