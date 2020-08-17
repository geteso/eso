## esoForum for Projects
***Projects* (esoForum for Projects) is a web forum software** created for the sandbox discussion site [esoForum](https://esotalk.net) with which anyone can easily **create a functional and speedy forum.**  It's a roughly 230¹ KB GZIP download.
<br>
<br>
![](https://image.ibb.co/g8KXuq/whole.png)

*Projects* is based off the now-defunct [esoTalk](#) forum software, created by Simon and [Toby Zerner](http://tobyzerner.com) — so remember that this isn't affiliated with the original project. Current development is provided by [esoForum](https://esotalk.net) administrators.

### Creating your own forum
**It's very easy to create your own forum** and doesn't require much (if any) command-line experience.  **For an extremely novice tutorial, see [this tutorial](https://github.com/esoForum/esoProjects/wiki/The-novice's-guide-to-creating-an-esoProject)** but otherwise dependencies are specified below.

1. **[Apache](https://apache.org)** or [lighttpd](https://www.lighttpd.net).
2. A recent (latest, recommended) version of **[MySQL](https://www.mysql.com)** and an empty database.
2. **[PHP 5.3-5.6](https://www.php.net/releases/5_6_0.php)** as version seven is not yet supported.
4. **PHP-MySQL and PHP-GD** extensions.

**For a production forum, you should serve all traffic over HTTPS/TLS.**  This can easily be done by installing [LetsEncrypt](https://letsencrypt.org/).  esoProjects requires no additional configuration for HTTPS/TLS other than changing the URL in your `config.php` from `http://example.com` -> `https://example.com`.

---

¹SIZE — File size measured in the unsplitted GZIP (.gz) lossless format.
