<?php
/**
 * WordPress基础配置文件。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以不使用网站，您需要手动复制这个文件，
 * 并重命名为“wp-config.php”，然后填入相关信息。
 *
 * 本文件包含以下配置选项：
 *
 * * MySQL设置
 * * 密钥
 * * 数据库表名前缀
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define('DB_NAME', 'a0915143937');

/** MySQL数据库用户名 */
define('DB_USER', 'a0915143937');

/** MySQL数据库密码 */
define('DB_PASSWORD', 'bedb2c19');

/** MySQL主机 */
define('DB_HOST', 'localhost');

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '=uWIDE8hh;xvzk3QnT_bzJ+_1qXU6E5[Zvpo%V{1]T,Zsv]xq>.),%[;xH2tJxkw');
define('SECURE_AUTH_KEY',  'nh30)M|[v1Oo<0qe:(CI4qro7-#Z//OjxXALv@JY`%j_Q0.R[.!]V;2d!?XXfQ ]');
define('LOGGED_IN_KEY',    '~.G;2?6&@K.x}M#UD2+Tg9M~z-0{pE8n!9V$Lqlc,=SgO*TQP.EKZI%(X{QaVUqT');
define('NONCE_KEY',        '4J6v4:oaEMq~x^TBqh$rv$Cs;Cardt,Ts.fTChBaRtI/12HwGvHb6$/t/@iSRrJ5');
define('AUTH_SALT',        ';BFhzg,9h};eL{UXv7i!s]]%qMZ$uX&I_Gc.5JVe[P(|EplP!<!I!&7aa!0o ~(0');
define('SECURE_AUTH_SALT', '!@SXg@ylwAg%PogY$b<GZ8;Art&:*,-Ept~0xwlV76;*I`~?Z%0oM_iOph:8/<Gl');
define('LOGGED_IN_SALT',   'mL!Z`a7<F!K<4)n!0sA>ZE[|X^#J5|Az+)5mugUoX1U r]7X$|xpn$L4-=e?O5RR');
define('NONCE_SALT',       '~c.<o<S`H%:wXnS.J?f(jz>oSX|r*{>x9M6IPfz{M0>=#1>lQ7c_d-yN+f8ldN6R');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix  = 'wp_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 *
 * 要获取其他能用于调试的信息，请访问Codex。
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/**
 * zh_CN本地化设置：启用ICP备案号显示
 *
 * 可在设置→常规中修改。
 * 如需禁用，请移除或注释掉本行。
 */
define('WP_ZH_CN_ICP_NUM', true);

/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');
