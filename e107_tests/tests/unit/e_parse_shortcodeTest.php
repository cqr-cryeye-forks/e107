<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class e_parse_shortcodeTest extends \Codeception\Test\Unit
{
	/** @var e_parse_shortcode */
	private $scParser;

	public function _before()
	{
		try
		{
			$this->scParser = $this->make('e_parse_shortcode');
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't create e_parse_shortcode object");
		}

		$this->scParser->__construct();
	}

//	public function testShortcode_SITELINKS_ALT()
//	{
//		$output = $this->scParser->parseCodes('{SITELINKS_ALT=/e107_themes/jayya/images/arrow.png+noclick}');
//		var_export($output);
//	}

	/*
	public function testIsBatchOverride()
	{

	}

	public function testIsRegistered()
	{

	}

	public function testIsOverride()
	{

	}

	public function testResetScClass()
	{

	}

	public function testDoCode()
	{

	}

	public function testGetScObject()
	{

	}
*/
	public function testParseCodesWithArray()
	{
		$text = '<ul class="dropdown-menu {LINK_SUB_OVERSIZED}" role="menu" >';

		$array = array(
			'LINK_TEXT' => 'Content',
		    'LINK_URL' => '#',
		    'ONCLICK' => '',
		    'SUB_HEAD' => '',
		    'SUB_MENU' => '',

		    'ID' => '',
		    'SUB_ID' => '',
		    'LINK_CLASS' =>  'e-expandit',
		    'SUB_CLASS' =>  'e-hideme e-expandme',
		    'LINK_IMAGE' =>  '',
		    'LINK_SUB_OVERSIZED' => 'oversized',
		    'LINK_BADGE' => '',
		);

		  // -- Legacy Wrapper --
        global $sc_style;
        $sc_style = array();
        $sc_style['LINK_SUB_OVERSIZED']['pre'] = "** ";
        $sc_style['LINK_SUB_OVERSIZED']['post'] = " **";

		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu ** oversized **" role="menu" >';
		$this->assertEquals($expected, $actual);

		// v2.x Array Wrapper - should override any $sc_style legacy wrapper
		$array['_WRAPPER_'] = "non-existent/template";
		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu oversized" role="menu" >';
		$this->assertEquals($expected, $actual);

	}


	public function testParseCodesWithClass()
	{
	    $sc = e107::getScBatch('_blank', true, '_blank');
	    $this->assertIsObject($sc);

        // - v1.x Wrapper Test.
        global $sc_style;
        $sc_style = array();
        $sc_style['BLANK_TEST']['pre'] = "** ";
        $sc_style['BLANK_TEST']['post'] = " **";

        $actualTemplate = e107::getTemplate('_blank', '_blank', 'default');
        $otherTemplate = e107::getTemplate('_blank', '_blank', 'other');


        $expectedTemplate = "<div>{BLANK_TEST}</div>";
        $this->assertEquals($expectedTemplate, $actualTemplate);
        $actualLegacy = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expectedLegacy = "<div>** test **</div>";
        $this->assertEquals($expectedLegacy, $actualLegacy);

        // - v2.x Wrapper Test.
        $sc->wrapper('_blank/default'); // overrides legacy $sc_style;
        $actual = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);

		// different template, same wrapper ID.
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);

		// different template and non-existent wrappers - should fallback to legacy wrappers and not use '_blank/default' wrappers by the same name.
        $sc->wrapper('_blank/other');
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
		$expected = "<div>** test **</div>";
        $this->assertEquals($expected, $actual);


        // And back to a wrapper that exists.
        $sc->wrapper('_blank/default'); // overrides legacy $sc_style;
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);


    }


    public function testAdminShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/admin_shortcodes.php");
        require_once(e_CORE."templates/admin_icons_template.php");
        require_once(e_LANGUAGEDIR.'English/admin/lan_header.php');
        require_once(e_LANGUAGEDIR.'English/admin/lan_footer.php');

        try
		{
			$sc = $this->make('admin_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }

    public function testBBcodeShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/bbcode_shortcodes.php");

        try
		{
			$sc = $this->make('bbcode_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }

    public function testCommentShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/comment_shortcodes.php");

        try
		{
			/** @var comment_shortcodes $sc */
			$sc = $this->make('comment_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

	   $values = array(
	        'comment_id'           => '84',
	        'comment_pid'          => '82',
	        'comment_item_id'      => '53',
	        'comment_subject'      => 'Re: New Item',
	        'comment_author_id'    => '1',
	        'comment_author_name'  => 'admin',
	        'comment_author_email' => 'someone@gmail.com',
	        'comment_datestamp'    => '1609767045',
	        'comment_comment'      => 'Nested Comment here',
	        'comment_blocked'      => '0',
	        'comment_ip'           => '0000:0000:0000:0000:0000:ffff:7f00:0001',
	        'comment_type'         => '0',
	        'comment_lock'         => '0',
	        'comment_share'        => '0',
	        'table'                 => 'news',
			'action'	            => '',
			'subject' 	            => 'subject name',
			'comval'	            => 'a comment',
			'itemid'	            => 5,
			'pid'		            => 3,
	        'eaction'	            => '',
	        'rate'		            => 2,
	        'user_id'               => 1,
	        'user_join'             => 1518441749
	   );

		$sc->setVars($values);

        $this->processShortcodeMethods($sc);

    }



    public function testContactShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/contact_shortcodes.php");

        try
		{
			$sc = $this->make('contact_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }


    public function testErrorShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/error_shortcodes.php");

        try
		{
			$sc = $this->make('error_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }


    public function testLoginShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/login_shortcodes.php");

        try
		{
			$sc = $this->make('login_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }

    public function testNavigationShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/navigation_shortcodes.php");

        try
		{
			/** @var navigation_shortcodes $sc */
			$sc = $this->make('navigation_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
		
		$vars = array(
			'link_id'          => '6',
			'link_name'        => 'News',
			'link_url'         => 'news.php',
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => '1',
			'link_order'       => '5',
			'link_parent'      => '0',
			'link_open'        => '0',
			'link_class'       => '0',
			'link_function'    => 'news::last_ten',
			'link_sefurl'      => 'index',
			'link_owner'       => 'news'
		);

		$sc->setVars($vars);
		
        $this->processShortcodeMethods($sc);

    }


    public function testNewsArchiveShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/news_archive_shortcodes.php");

        try
		{
			$sc = $this->make('news_archive_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'news_id'       => 1,
			'news_title'    => "my title",
			'news_datestamp'    => time(),
			'category_name'     => "my category",
			'user_id'           => 1,
			'user_name'         => 'admin'
		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


    public function testNewsShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/news_shortcodes.php");

        try
		{
			/** @var news_shortcodes $sc */
			$sc = $this->make('news_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'news_id'               => '1',
			'news_title'            => 'Welcome',
			'news_sef'              => 'welcome-to-e107-me-again-x',
			'news_body'             => '[html]<p>Lorem ipsum dolor sit amet, no meis semper dicunt est, petentium eloquentiam quo ne. At vero facer eam. Ex nam altera oportere, nisl natum prima id pro. Rebum augue dissentiet eum te, vel veniam eirmod option ea, at eos velit repudiare. Ius sumo dicit adolescens id, an cum efficiantur concludaturque.<br><br>Summo sensibus cum ne, et duo torquatos conceptam. No aeque elitr constituam qui. Nostro corpora nec no, diam verterem tincidunt has et. Altera accumsan urbanitas pro eu, ei assum voluptaria sed. Eam tibique nominavi consequuntur an.<br><br>Ei perfecto delicata usu, quo eius noster blandit te. Eu doctus volumus pri. Meis argumentum an nam, eos odio prima autem an. Te complectitur intellegebat pro, ius id alterum maiestatis. Ea facer accusata sed, ex illum antiopam quo.<br><br>Altera putent pri ad, in phaedrum dissentiunt per. Te eum everti dolores. Ut mea vero autem viderer, mel brute harum senserit id. Minim senserit eloquentiam duo in, sit ei justo graece petentium. Sea id homero oporteat invenire.<br><br>Pri semper dolorum ad. Cu eius repudiare eos. Eum in eleifend necessitatibus. Ne has mutat intellegebat.ok asdasdasd asdasd okay. okayasdasd okokokok wowow sdf okoko lk</p>[/html]',
			'news_extended'         => '[html]<p><strong>Lorem ipsum</strong> is a pseudo-Latin text used in web design, typography, layout, and printing in place of English to emphasise design elements over content. It&#039;s also called placeholder (or filler) text. It&#039;s a convenient tool for mock-ups. It helps to outline the visual elements of a document or presentation, eg typography, font, or layout. Lorem ipsum is mostly a part of a Latin text by the classical author and philosopher Cicero. Its words and letters have been changed by addition or removal, so to deliberately render its content nonsensical; it&#039;s not genuine, correct, or comprehensible Latin anymore.</p>
			<p>While <strong>lorem ipsum</strong>&#039;s still resembles classical Latin, it actually has no meaning whatsoever. As Cicero&#039;s text doesn&#039;t contain the letters K, W, or Z, alien to latin, these, and others are often inserted randomly to mimic the&nbsp; typographic appearence of European languages, as are digraphs not to be found in the original.</p>[/html]',
			'news_meta_keywords'    => 'welcome,new website',
			'news_meta_description' => 'Description for Facebook and search engines.',
			'news_meta_robots'      => '',
			'news_datestamp'        => '1454367600',
			'news_author'           => '1',
			'news_category'         => '1',
			'news_allow_comments'   => '0',
			'news_start'            => '0',
			'news_end'              => '0',
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_comment_total'    => '0',
			'news_summary'          => 'Example news item summary there',
			'news_thumbnail'        => '{e_THEME}agency2/install/news/deer.jpg,,,,',
			'news_sticky'           => '0',
			'news_template'         => 'default'
		);

	//	$sc->setVars($vars);
		$sc->__construct();
		$sc->setScVar('news_item', $vars);
		$sc->setScVar('param', array('current_action'=>'list'));

        $this->processShortcodeMethods($sc);

    }

	 public function testPageShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/page_shortcodes.php");

        try
		{
			/** @var cpage_shortcodes $sc */
			$sc = $this->make('cpage_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
		
		$vars =  array('page_id' => '1',
			'page_title' => 'Article 1',
			'page_subtitle' => 'My subtitle goes here.',
			'page_sef' => 'article-1',
			'page_chapter' => '2',
			'page_metakeys' => 'keywords',
			'page_metadscr' => 'Meta Description',
			'page_metarobots' => 'noindex',
			'page_text' => '[html]<p>Lorem ipsum dolor sit amet, <sup>1</sup> consectetur adipiscing elit. Donec libero ipsum; imperdiet at risus non, dictum sagittis odio! Nulla facilisi. Pellentesque adipiscing facilisis pharetra. Morbi imperdiet augue in ligula luctus, et iaculis est porttitor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ultricies vitae nisi ut porttitor. Curabitur lectus tellus, feugiat a elit vel, gravida iaculis dui. Nulla vulputate turpis dui, ac faucibus enim dignissim non. Ut non tellus suscipit, scelerisque orci sed, scelerisque sapien. Aenean convallis sodales nulla in porttitor. In pretium ante sapien, a tempor eros blandit nec <sup>2</sup>.<br><br>Nulla non est nibh? Fusce lacinia quam adipiscing magna posuere dapibus. Sed mollis condimentum rhoncus. Morbi sollicitudin tellus a ligula luctus, ac varius arcu ullamcorper. Mauris in aliquet tellus, nec porttitor dui. Quisque interdum euismod mi sed bibendum. Vivamus non odio quis quam lacinia rhoncus in nec nibh. Integer vitae turpis condimentum, laoreet diam nec viverra fusce.</p>[/html]',
			'page_author' => '1',
			'page_datestamp' => '1371420000',
			'page_rating_flag' => '1',
			'page_comment_flag' => '1',
			'page_password' => '',
			'page_class' => '0',
			'page_ip_restrict' => '',
			'page_template' => 'default',
			'page_order' => '20',
			'page_fields' => NULL,'menu_name' => '',
			'menu_title' => 'Heading 1',
			'menu_text' => '[html]<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus auctor egestas condimentum. Suspendisse placerat nunc orci, a ultrices tortor eleifend in. Vestibulum tincidunt fringilla malesuada? Phasellus dolor risus, aliquam eu odio quis, mattis cursus magna. Integer ut blandit purus; vitae posuere ante. Vivamus sapien nisl, pulvinar vel turpis a, malesuada vehicula lorem! Curabitur magna justo; laoreet at congue sit amet, tincidunt sit amet erat. Integer vehicula eros quis odio tincidunt, nec dapibus sem molestie. Cras sed viverra eros. Nulla ut lectus tellus.</p>[/html]',
			'menu_image' => '{e_THEME}steminst_eu/_content/2019-07/chromosome_dna_pattern_genetic_3_d_psychedelic_1920x1200.jpg',
			'menu_icon' => '',
			'menu_template' => 'button',
			'menu_class' => '0',
			'menu_button_url' => '',
			'menu_button_text' => '')
			;


		$sc->setVars($vars);

		$exclude = array('sc_cpagemessage'); // system messages

        $this->processShortcodeMethods($sc, $exclude);

    }

     public function testPageEShortcodes()
    {
        require_once(e_PLUGIN."page/e_shortcode.php");

        try
		{
			/** @var page_shortcodes $sc */
			$sc = $this->make('page_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array('page_id' => '1',
/*			'page_title' => 'Article 1',
			'page_subtitle' => 'My subtitle goes here.',
			'page_sef' => 'article-1',
			'page_chapter' => '2',
			'page_metakeys' => 'keywords',
			'page_metadscr' => 'Meta Description',
			'page_metarobots' => 'noindex',
			'page_text' => '[html]<p>Lorem ipsum dolor sit amet, <sup>1</sup> consectetur adipiscing elit. Donec libero ipsum; imperdiet at risus non, dictum sagittis odio! Nulla facilisi. Pellentesque adipiscing facilisis pharetra. Morbi imperdiet augue in ligula luctus, et iaculis est porttitor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ultricies vitae nisi ut porttitor. Curabitur lectus tellus, feugiat a elit vel, gravida iaculis dui. Nulla vulputate turpis dui, ac faucibus enim dignissim non. Ut non tellus suscipit, scelerisque orci sed, scelerisque sapien. Aenean convallis sodales nulla in porttitor. In pretium ante sapien, a tempor eros blandit nec <sup>2</sup>.<br><br>Nulla non est nibh? Fusce lacinia quam adipiscing magna posuere dapibus. Sed mollis condimentum rhoncus. Morbi sollicitudin tellus a ligula luctus, ac varius arcu ullamcorper. Mauris in aliquet tellus, nec porttitor dui. Quisque interdum euismod mi sed bibendum. Vivamus non odio quis quam lacinia rhoncus in nec nibh. Integer vitae turpis condimentum, laoreet diam nec viverra fusce.</p>[/html]',
			'page_author' => '1',
			'page_datestamp' => '1371420000',
			'page_rating_flag' => '1',
			'page_comment_flag' => '1',
			'page_password' => '',
			'page_class' => '0',
			'page_ip_restrict' => '',
			'page_template' => 'default',
			'page_order' => '20',
			'page_fields' => NULL,'menu_name' => '',
			'menu_title' => 'Heading 1',
			'menu_text' => '[html]<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus auctor egestas condimentum. Suspendisse placerat nunc orci, a ultrices tortor eleifend in. Vestibulum tincidunt fringilla malesuada? Phasellus dolor risus, aliquam eu odio quis, mattis cursus magna. Integer ut blandit purus; vitae posuere ante. Vivamus sapien nisl, pulvinar vel turpis a, malesuada vehicula lorem! Curabitur magna justo; laoreet at congue sit amet, tincidunt sit amet erat. Integer vehicula eros quis odio tincidunt, nec dapibus sem molestie. Cras sed viverra eros. Nulla ut lectus tellus.</p>[/html]',
			'menu_image' => '{e_THEME}steminst_eu/_content/2019-07/chromosome_dna_pattern_genetic_3_d_psychedelic_1920x1200.jpg',
			'menu_icon' => '',
			'menu_template' => 'button',
			'menu_class' => '0',
			'menu_button_url' => '',
			'menu_button_text' => '',*/
			'chapter_id' => '1',
			'chapter_parent' => '0',
			'chapter_name' => 'General',
			'chapter_sef' => 'general',
			'chapter_meta_description' => 'Lorem ipsum dolor sit amet.',
			'chapter_meta_keywords' => '',
			'chapter_manager' => '0',
			'chapter_icon' => '',
			'chapter_image' => '',
			'chapter_order' => '0',
			'chapter_template' => '',
			'chapter_visibility' => '0',
			'chapter_fields' => NULL

			)
			;


		$sc->setVars($vars);

	//	$exclude = array('sc_cpagemessage'); // system messages

        $this->processShortcodeMethods($sc);

    }


    public function testSignupShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/signup_shortcodes.php");

        try
		{
			$sc = $this->make('signup_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$exclude = array('sc_signup_coppa_text'); // uses random email obfiscation.
        $this->processShortcodeMethods($sc, $exclude);

    }


	public function testSitedownShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/sitedown_shortcodes.php");

        try
		{
			$sc = $this->make('sitedown_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

        $this->processShortcodeMethods($sc);

    }

    public function testUserShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/user_shortcodes.php");

        try
		{
			/** @var user_shortcodes $sc */
			$sc = $this->make('user_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
		
		$vars = array(
			'user_id'           => '1',
			'user_name'         => 'admin',
			'user_loginname'    => 'admin',
			'user_customtitle'  => 'Administrator',
			'user_password'     => '$2y$10$EfCajR8.i3G1Qu82VKwSzu4dOroWabexa9B10LFYuEqepSD4gzzWa',
			'user_sess'         => '',
			'user_email'        => 'myemail@gmail.com',
			'user_signature'    => '',
			'user_image'        => 'myimage.jpeg',
			'user_hideemail'    => '0',
			'user_join'         => '1518441749',
			'user_lastvisit'    => '1609890429',
			'user_currentvisit' => '1609953446',
			'user_lastpost'     => '1609793616',
			'user_chats'        => '1',
			'user_comments'     => '52',
			'user_ip'           => '123.45.678.91',
			'user_ban'          => '0',
			'user_prefs'        => '',
			'user_visits'       => '766',
			'user_admin'        => '1',
			'user_login'        => 'Real Name',
			'user_class'        => '12,14,15,16,6',
			'user_perms'        => '0',
			'user_realm'        => '',
			'user_pwchange'     => '1518441749',
			'user_xup'          => ''

			);

		$sc->setVars($vars);

		$exclude = array('sc_user_email'); // uses random obfiscation.
        $this->processShortcodeMethods($sc, $exclude);

    }


	public function testUserSettingsShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/usersettings_shortcodes.php");

        try
		{
			/** @var user_shortcodes $sc */
			$sc = $this->make('usersettings_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'user_id'           => '1',
			'user_name'         => 'admin',
			'user_loginname'    => 'admin',
			'user_customtitle'  => 'Administrator',
			'user_password'     => '$2y$10$EfCajR8.i3G1Qu82VKwSzu4dOroWabexa9B10LFYuEqepSD4gzzWa',
			'user_sess'         => '',
			'user_email'        => 'myemail@gmail.com',
			'user_signature'    => '',
			'user_image'        => 'myimage.jpeg',
			'user_hideemail'    => '0',
			'user_join'         => '1518441749',
			'user_lastvisit'    => '1609890429',
			'user_currentvisit' => '1609953446',
			'user_lastpost'     => '1609793616',
			'user_chats'        => '1',
			'user_comments'     => '52',
			'user_ip'           => '123.45.678.91',
			'user_ban'          => '0',
			'user_prefs'        => '',
			'user_visits'       => '766',
			'user_admin'        => '1',
			'user_login'        => 'Real Name',
			'user_class'        => '12,14,15,16,6',
			'user_perms'        => '0',
			'user_realm'        => '',
			'user_pwchange'     => '1518441749',
			'user_xup'          => '',
			'userclass_list'    => USERCLASS_LIST

			);

		$sc->__construct();
		$sc->setVars($vars);

		// these are tested in the user-extended test.
		$exclude = array('sc_userextended_all', 'sc_userextended_cat', 'sc_userextended_field'); // uses e107::setRegistry() to avoid duplicate rendering.
        $this->processShortcodeMethods($sc, $exclude);

    }

// -------------- Plugins ------------------------


    public function testChatboxMenuShortcodes()
    {
        require_once(e_PLUGIN."chatbox_menu/chatbox_menu_shortcodes.php");

        try
		{
			/** @var chatbox_menu_shortcodes $sc */
			$sc = $this->make('chatbox_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'cb_id'        => '11',
			'cb_nick'      => '1.admin',
			'cb_message'   => 'A new chatbox comment',
			'cb_datestamp' => '1609613065',
			'cb_blocked'   => '0',
			'cb_ip'        => '0000:0000:0000:0000:0000:ffff:7f00:0001'
		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }

      public function testCommentMenuShortcodes()
    {
        require_once(e_PLUGIN."comment_menu/comment_menu_shortcodes.php");

        try
		{
			/** @var comment_menu_shortcodes $sc */
			$sc = $this->make('comment_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

	   $values = array(
	        'comment_id'           => '84',
	        'comment_pid'          => '82',
	        'comment_item_id'      => '53',
	        'comment_subject'      => 'Re: New Item',
	        'comment_author_id'    => '1',
	        'comment_author_name'  => 'admin',
	        'comment_author_email' => 'someone@gmail.com',
	        'comment_datestamp'    => '1609767045',
	        'comment_comment'      => 'Nested Comment here',
	        'comment_blocked'      => '0',
	        'comment_ip'           => '0000:0000:0000:0000:0000:ffff:7f00:0001',
	        'comment_type'         => '0',
	        'comment_lock'         => '0',
	        'comment_share'        => '0',
	        'table'                 => 'news',
			'action'	            => '',
			'subject' 	            => 'subject name',
			'comval'	            => 'a comment',
			'itemid'	            => 5,
			'pid'		            => 3,
	        'eaction'	            => '',
	        'rate'		            => 2,
	        'user_id'               => 1,
	        'user_join'             => 1518441749,
	        'comment_type'          => 'Type',
	        'comment_title'         => "Title",
	        'comment_url'           => e_HTTP."page.php?3",
	        'comment_author'        => 'admin',
			'comment_author_image'  => '',

	   );

		$sc->setVars($values);
        $this->processShortcodeMethods($sc);

    }


    public function testDownloadShortcodes()
    {
        require_once(e_PLUGIN."download/download_shortcodes.php");

        try
		{
			/** @var download_shortcodes $sc */
			$sc = $this->make('download_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array(
			'download_id'             => '1',
			'download_name'           => 'MyFile v1',
			'download_url'            => '{e_MEDIA_FILE}2016-03/myfile.zip',
			'download_sef'            => 'italk-v1',
			'download_author'         => 'admin',
			'download_author_email'   => 'email@gmail.com',
			'download_author_website' => 'https://somewhere.com',
			'download_description'    => 'description of my file',
			'download_keywords'       => 'keyword1,keyword2',
			'download_filesize'       => '654432',
			'download_requested'      => '4',
			'download_category'       => '2',
			'download_active'         => '1',
			'download_datestamp'      => '1560544675',
			'download_thumb'          => '',
			'download_image'          => '',
			'download_comment'        => '1',
			'download_class'          => '0',
			'download_mirror'         => '',
			'download_mirror_type'    => '0',
			'download_visible'        => '0',
			'download_category_id'    => '2',
			'download_category_name'  => 'My Category',
			'download_category_description' => 'My Category Description',
			'download_category_icon'    => '',
			'download_category_parent'  => '0',
			'download_category_class'   => '0',
			'download_category_order'   => '1',
			'download_category_sef'     => 'my-category'

		);

		$sc->__construct();

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }

    public function testFaqsShortcodes()
    {
        require_once(e_PLUGIN."faqs/faqs_shortcodes.php");

        try
		{
			/** @var faqs_shortcodes $sc */
			$sc = $this->make('faqs_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
		'faq_id'        => '4',
		'faq_parent'    => '1',
		'faq_question'  => 'My Second Question which is quite long and might wrap to another line after that',
		'faq_answer'    => '[html]<p>My Second Answer</p>[/html]',
		'faq_comment'   => '0',
		'faq_datestamp' => '1461263100',
		'faq_author'    => '1',
		'faq_author_ip' => '',
		'faq_tags'      => '',
		'faq_order'     => '2',
		'faq_info_id'   => '2',
		'faq_info_title'  => 'Misc',
		'faq_info_about'  => 'Other FAQs',
		'faq_info_parent' => '0',
		'faq_info_class'  => '0',
		'faq_info_order'  => '1',
		'faq_info_icon'   => '',
		'faq_info_metad'  => 'description',
		'faq_info_metak'  => 'keyword1,keyword2',
		'faq_info_sef'    => 'misc'

		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


    public function testForumShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/forum_shortcodes.php");

        try
		{
			/** @var forum_shortcodes $sc */
			$sc = $this->make('forum_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL

		);

		$sc->__construct();

		$sc->setVars($vars);

		$exclude = array('sc_info'); // uses time with seconds.

        $this->processShortcodeMethods($sc);

    }

      public function testForumPostShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/post_shortcodes.php");

        try
		{
			/** @var plugin_forum_post_shortcodes $sc */
			$sc = $this->make('plugin_forum_post_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL,
			'thread_id' => '1',
			'thread_name' => '3 Duis tempus enim vitae magna placerat vel dapibus tellus feugiat.',
			'thread_forum_id' => '4',
			'thread_views' => '53',
			'thread_active' => '1',
			'thread_lastpost' => '1434584999',
			'thread_sticky' => '0',
			'thread_datestamp' => '1367307189',
			'thread_user' => '2',
			'thread_user_anon' => NULL,
			'thread_lastuser' => '1',
			'thread_lastuser_anon' => NULL,
			'thread_total_replies' => '7',
			'thread_options' => NULL,
			'post_id' => '1',
			'post_entry' => '4 Morbi eleifend auctor quam, ac consequat ipsum dictum vitae. Curabitur egestas lacinia mi, in venenatis mi euismod eu.',
			'post_thread' => '1',
			'post_forum' => '4',
			'post_status' => '0',
			'post_datestamp' => '1367307189',
			'post_user' => '2',
			'post_edit_datestamp' => NULL,
			'post_edit_user' => NULL,
			'post_ip' => NULL,
			'post_user_anon' => NULL,
			'post_attachments' => NULL,
			'post_options' => NULL


		);

		$sc->__construct();

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


      public function testForumViewShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/view_shortcodes.php");

        try
		{
			/** @var plugin_forum_view_shortcodes $sc */
			$sc = $this->make('plugin_forum_view_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL,
			'thread_id' => '1',
			'thread_name' => '3 Duis tempus enim vitae magna placerat vel dapibus tellus feugiat.',
			'thread_forum_id' => '4',
			'thread_views' => '53',
			'thread_active' => '1',
			'thread_lastpost' => '1434584999',
			'thread_sticky' => '0',
			'thread_datestamp' => '1367307189',
			'thread_user' => '2',
			'thread_user_anon' => NULL,
			'thread_lastuser' => '1',
			'thread_lastuser_anon' => NULL,
			'thread_total_replies' => '7',
			'thread_options' => NULL,
			'post_id' => '1',
			'post_entry' => '4 Morbi eleifend auctor quam, ac consequat ipsum dictum vitae. Curabitur egestas lacinia mi, in venenatis mi euismod eu.',
			'post_thread' => '1',
			'post_forum' => '4',
			'post_status' => '0',
			'post_datestamp' => '1367307189',
			'post_user' => 1,
			'post_edit_datestamp' => NULL,
			'post_edit_user' => NULL,
			'post_ip' => NULL,
			'post_user_anon' => NULL,
			'post_attachments' => NULL,
			'post_options' => NULL,
			'user_join'     => time(),
			'user_id'       => 1,
			'user_name'     => USERNAME,
			'user_hideemail'    => 1,
			'user_plugin_forum_posts' => 3,
			'user_visits' => 6,
			'user_admin' => 1,
			'user_join' => time() - 8000,
		);

		$sc->__construct();

		$sc->setVars($vars);
		$sc->setScVar('postInfo', $vars);

        $this->processShortcodeMethods($sc);

    }


    public function testGalleryShortcodes()
    {
        require_once(e_PLUGIN."gallery/e_shortcode.php");

        try
		{
			/** @var gallery_shortcodes $sc */
			$sc = $this->make('gallery_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'media_id'           => '227',
			'media_type'         => 'image/jpeg',
			'media_name'         => 'gasmask.jpg',
			'media_caption'      => 'gasmask.jpg',
			'media_description'  => '',
			'media_category'     => '_common_image',
			'media_datestamp'    => '1464646050',
			'media_author'       => '1',
			'media_url'          => '{e_THEME}voux/install/gasmask.jpg',
			'media_size'         => '91054',
			'media_dimensions'   => '1200 x 830',
			'media_userclass'    => '0',
			'media_usedby'       => '',
			'media_tags'         => '',
			'media_cat_id'       => '1',
			'media_cat_owner'    => '_common',
			'media_cat_category' => '_common_image',
			'media_cat_title'    => '(Common Images)',
			'media_cat_sef'      => '',
			'media_cat_diz'      => 'Media in this category will be available in all areas of admin.',
			'media_cat_class'    => '253',
			'media_cat_image'    => '',
			'media_cat_order'    => '0'
		);



		$sc->setVars($vars);
		$exclude = array('sc_gallery_slides'); // uses a counter.
        $this->processShortcodeMethods($sc, $exclude);

    }


    public function testHeroShortcodes()
    {
        require_once(e_PLUGIN."hero/hero_shortcodes.php");

        try
		{
			/** @var plugin_hero_hero_shortcodes $sc */
			$sc = $this->make('plugin_hero_hero_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array(
				'hero_id' => '1',
				'hero_title' => 'A [powerful] &amp; [free] hero area',
				'hero_description' => '[Easy] to Use',
				'hero_bg' => '{e_MEDIA_IMAGE}2020-12/4.sm.webp',
				'hero_media' => '{e_MEDIA_IMAGE}2020-12/horse.jpg',
				'hero_bullets' => e107::unserialize('[
		    {
		        "icon": "fa-sun-o.glyph",
		        "icon_style": "warning",
		        "text": "Add some bullet text",
		        "animation": "fadeInRight",
		        "animation_delay": "15"
		    },
		    {
		        "icon": "fa-font-awesome.glyph",
		        "icon_style": "success",
		        "text": "Select an Icon from FontAwesome or others",
		        "animation": "fadeInRight",
		        "animation_delay": "25"
		    },
		    {
		        "icon": "fa-adjust.glyph",
		        "icon_style": "danger",
		        "text": "Choose a Style from Bootstrap",
		        "animation": "fadeInRight",
		        "animation_delay": "35"
		    },
		    {
		        "icon": "",
		        "icon_style": "",
		        "text": "",
		        "animation": "",
		        "animation_delay": "0"
		    },
		    {
		        "icon": "",
		        "icon_style": "",
		        "text": "",
		        "animation": "",
		        "animation_delay": "0"
		    }
		]'),
		'hero_button1' => e107::unserialize('{
		    "icon": "fa-",
		    "label": "",
		    "url": "",
		    "class": ""
		}'),
		'hero_button2' => e107::unserialize('{
		    "icon": "fa-",
		    "label": "",
		    "url": "",
		    "class": ""
		}'),
		'hero_order' => '1',
		'hero_class' => '0'
		);



		$sc->setVars($vars);
	//	$exclude = array('sc_gallery_slides'); // uses a counter.
        $this->processShortcodeMethods($sc);

    }


    public function testLoginMenuShortcodes()
    {
        require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");

        try
		{
			/** @var login_menu_shortcodes $sc */
			$sc = $this->make('login_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}



        $this->processShortcodeMethods($sc);

    }

     public function testOnlineShortcodes()
    {
        require_once(e_PLUGIN."online/online_shortcodes.php");

        try
		{
			/** @var online_shortcodes $sc */
			$sc = $this->make('online_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testPMShortcodes()
    {
        require_once(e_PLUGIN."pm/pm_shortcodes.php");

        try
		{
			/** @var plugin_pm_pm_shortcodes $sc */
			$sc = $this->make('plugin_pm_pm_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$vars = array(
			'pm_id' => 5,
			'pm_sent' => time(),
			'pm_read' => 0,
			'pm_from' => 1,
			'from_name' => 'admin',
			'pm_to' => 1,
			'pm_block_datestamp' => time(),
			'pm_block_from'=> 2,

		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


	public function testRSSShortcodes()
    {
        require_once(e_PLUGIN."rss_menu/rss_shortcodes.php");

        try
		{
			/** @var rss_menu_shortcodes $sc */
			$sc = $this->make('rss_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$vars =  array(
			'rss_id'        => '1',
			'rss_name'      => 'News',
			'rss_url'       => 'news',
			'rss_topicid'   => '0',
			'rss_path'      => '0',
			'rss_text'      => 'The rss feed of the news',
			'rss_datestamp' => '1456448477',
			'rss_class'     => '0',
			'rss_limit'     => '10',
			// import shortcodes. 
			'name'		    => "Comments",
			'url'           => 'comments',
			'topic_id'      => '',
			'path'		    => 'comments',
			'text'		    => 'the rss feed of comments',
			'class'		    => '0',
			'limit'		    => '9',
		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }



	public function testSigninShortcodes()
    {
        require_once(e_PLUGIN."signin/signin_shortcodes.php");

        try
		{
			/** @var plugin_signin_signin_shortcodes $sc */
			$sc = $this->make('plugin_signin_signin_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testListShortcodes()
    {
        require_once(e_PLUGIN."list_new/list_shortcodes.php");
        require_once(e_PLUGIN."list_new/list_class.php");

        try
		{
			/** @var list_shortcodes $sc */
			$sc = $this->make('list_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->rc = new listclass;

		$vars = array (
			'caption' => 'My Caption',
			 'display' => '1',
			 'open' => '1',
			 'author' => '',
			 'category' => '1',
			 'date' => '',
			 'icon' => '',
			 'amount' => '1',
			 'order' => '1',
			 'section' => 'news',
        );


		$sc->row = $vars;

		$exclude = array('sc_list_category');  // unknown issue.

        $this->processShortcodeMethods($sc, $exclude);

    }




	/*


            e107_plugins\links_page  (1 usage found)
                links_page_shortcodes.php  (1 usage found)
                    1 <?php


	 */


// ------------------------------------------------


    private function processShortcodeMethods($sc, $exclude=array())
    {
    	$list = get_class_methods($sc);

        foreach($list as $meth)
        {
            if(strpos($meth, 'sc_') !== 0 || in_array($meth, $exclude))
            {
                continue;
            }

            $scName = '{'.strtoupper(str_replace('sc_', '', $meth)).'}';

            $result = e107::getParser()->parseTemplate($scName, true, $sc);
            $expected = $sc->$meth();

            $this->assertEquals($expected,$result, $scName.' != '.$meth.'()');
        }



    }


/*
	public function testInitShortcodeClass()
	{

	}

	public function testRegisterShortcode()
	{

	}

	public function testSetScVar()
	{

	}

	public function testCallScFunc()
	{

	}

	public function testIsScClass()
	{

	}

	public function testParse_scbatch()
	{

	}

	public function testLoadThemeShortcodes()
	{

	}
	*/
}
