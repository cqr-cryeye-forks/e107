<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-02-06 00:23:28 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/*
e_loginbox.php example:

//Example link 1
$LBOX_LINK = array();
$LBOX_LINK['link_label']  = 'My link 1';
$LBOX_LINK['link_url']    = e_PLUGIN_ABS.'myplug/me.php?1';
$lbox_links[] = $LBOX_LINK;

//Example stats
$LBOX_STAT = array();
$LBOX_STAT['stat_item']  = 'my item';
$LBOX_STAT['stat_items']  = 'my items';
$LBOX_STAT['stat_new']    = '1';
$LBOX_STAT['stat_nonew']    = 'no my items';//or empty to omit
$lbox_stats[] = $LBOX_STAT;
*/

class login_menu_class
{
    function get_coreplugs($active=true) {
        global $pref;
        
        $list = array('forum', 'chatbox_menu');
        $ret = array();
        
        foreach ($list as $value) {
            if(!$active || array_key_exists($value, $pref['plug_installed']))
                $ret[] = $value;
        }

        return $ret;
    }
    
    function get_external_list($sort = true) {
        global $sql, $pref, $menu_pref;
        
        require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$list = array();
		
		$list_arr = $fl->get_files(e_PLUGIN, "e_loginbox\.php$", "standard", 1);
		
		if($list_arr) {
            foreach ($list_arr as $item) {
                $tmp = end(explode('/', trim($item['path'], '/.')));
                
                if(array_key_exists($tmp, $pref['plug_installed'])) {
                    $list[] = $tmp;
                }
            }
        }

        if($sort && $menu_pref['login_menu']['external_links']) {
            $tmp = array_flip(explode(',', $menu_pref['login_menu']['external_links']));
            
            $cnt = count($tmp);
            foreach ($list as $value) {
            	$list_ord[$value] = varset($tmp[$value], $cnt++);
            }
            
            asort($list_ord);
            $list = array_keys($list_ord);
            unset($list_ord);
        }
        
		return $list;
    }
    
    function parse_external_list($active=false, $order=true) {
        global $menu_pref;
        //prevent more than 1 call
        if(($tmp = getcachedvars('loginbox_elist')) !== FALSE) return $tmp;
        
        $ret = array();
        $lbox_admin = varsettrue($eplug_admin, false);
        $coreplugs = login_menu_class::get_coreplugs(); 
        
        $lprefs = $menu_pref['login_menu']['external_links'] ? explode(',', $menu_pref['login_menu']['external_links']) : array();
        $sprefs = $menu_pref['login_menu']['external_stats'] ? explode(',', $menu_pref['login_menu']['external_stats']) : array();
        
        if($active) {
            $tmp =  array_flip($lprefs);
            $tmp1 = array_flip($sprefs);
            $list = array_keys(array_merge($tmp, $tmp1));
        } else {
            $list = array_merge($coreplugs, login_menu_class::get_external_list($order));
        } 
        
        foreach ($list as $item) { 
        
            //core
            if(in_array($item, $coreplugs)) {           
                if($tmp = call_user_func(array('login_menu_class', "get_{$item}_stats"), $get_stats))
                    $ret['stats'][$item] = $tmp;  
                       
                continue;
            }
        	    
            $lbox_links = array();
            $lbox_stats = array();
            $lbox_links_active = (!$active || in_array($item, $lprefs));
            $lbox_stats_active = (!$active || in_array($item, $sprefs));

        	if(file_exists(e_PLUGIN.$item."/e_loginbox.php")) { 

                
                include(e_PLUGIN.$item."/e_loginbox.php");
                
                if(!empty($lbox_links) && $lbox_links_active) $ret['links'][$item] = $lbox_links;
                if(!empty($lbox_stats) && $lbox_stats_active) $ret['stats'][$item] = $lbox_stats;
                
            }
        }
        cachevars('loginbox_elist', $ret);
        
        return $ret;
    }
    
    function get_forum_stats($get_stats=true) {
        global $sql, $pref;
        
        if(!array_key_exists('forum', $pref['plug_installed']))
            return array();
        
        $lbox_stats = array();
        $lbox_stats[0]['stat_item']    = LOGIN_MENU_L20;
        $lbox_stats[0]['stat_items']   = LOGIN_MENU_L21;
        $lbox_stats[0]['stat_new']     = 0;
        $lbox_stats[0]['stat_nonew']   = LOGIN_MENU_L26.' '.LOGIN_MENU_L21;
        if($get_stats) {

            $nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
        	$qry = "
        	SELECT  count(*) as count FROM #forum_t  as t
        	LEFT JOIN #forum as f
        	ON t.thread_forum_id = f.forum_id
        	WHERE t.thread_datestamp > ".USERLV." and f.forum_class IN (".USERCLASS_LIST.") AND NOT (f.forum_class REGEXP ".$nobody_regexp.")
        	";
        	
        	if($sql->db_Select_gen($qry)) {
        		$row = $sql->db_Fetch();
        		$lbox_stats['forum'][0]['stat_new'] = $row['count'];
        	}
        }
    	
    	return $lbox_stats;
    }
    
    function get_chatbox_menu_stats() {
        global $sql, $pref;
        
        if(!array_key_exists('chatbox_menu', $pref['plug_installed']))
            return array();
        
        $lbox_stats[0]['stat_item']     = LOGIN_MENU_L16;
        $lbox_stats[0]['stat_items']    = LOGIN_MENU_L17;
        $lbox_stats[0]['stat_new']      = 0;
        $lbox_stats[0]['stat_nonew']    = LOGIN_MENU_L26.' '.LOGIN_MENU_L17;
        if($get_stats) {
            $lbox_stats['chatbox_menu'][0]['stat_new']  = $sql->db_Count('chatbox', '(*)', 'WHERE `cb_datestamp` > '.USERLV);
        }
        
        return $lbox_stats;
    }
    
    function render_config_links() {
        global $menu_pref;
        
        $ret = '';
        
        $lbox_infos = login_menu_class::parse_external_list(false);
        if(!varsettrue($lbox_infos['links'])) return '';
        
        $enabled = varsettrue($menu_pref['login_menu']['external_links']) ? explode(',', $menu_pref['login_menu']['external_links']) : array();
        
        $num = 1;
        foreach ($lbox_infos['links'] as $id => $stack) {
            $links = array();
            foreach ($stack as $value) {
            	$links[] = '<a href="'.$value['link_url'].'">'.varsettrue($value['link_label'], '['.LOGIN_MENU_L44.']').'</a>';
            }
            
            $plug_data = login_menu_class::get_plugin_data($id);
            
            $links = implode(', ', $links);
            
        	$ret .= '
            	<tr>
            	<td class="forumheader3">'.LOGIN_MENU_L37.' '.(varset($plug_data['eplug_name']) ? LOGIN_MENU_L45.LOGIN_MENU_L45a." {$plug_data['eplug_name']} ".LOGIN_MENU_L45b."<br />" : '').$links.'</td>
            	<td style="text-align: left;" class="forumheader3">

                   <table style="margin-left: 0px">
            	   <tr>
                    <td>
                	   <input type="checkbox" name="external_links['.$id.']" value="1"'.(in_array($id, $enabled) ? ' checked="checked"' : '').' />
                    </td>
                    <td>
                        '.LOGIN_MENU_L43.': <input type="text" class="tbox" style="text-align: right" size="4" maxlength="3" name="external_links_order['.$id.']" value="'.$num.'" />
                    </td>                   
                   </tr>
                   </table>
                   
                </td>
            	</tr>
            ';
            $num++;
        }
        
        if($ret) {
            $ret = '<tr><td colspan="2" class="fcaption">'.LOGIN_MENU_L38.'</td></tr>'.$ret;
        }
        
        return $ret;
    }
    
    function render_config_stats() {
        global $menu_pref;
        
        $ret = '';
        $lbox_infos = login_menu_class::parse_external_list(false);
        $lbox_infos = varsettrue($lbox_infos['stats'], array());

        if(!$lbox_infos) return '';

        $enabled = varsettrue($menu_pref['login_menu']['external_stats']) ? explode(',', $menu_pref['login_menu']['external_stats']) : array();
        
        $num = 1;
        foreach ($lbox_infos as $id => $stack) {

            $plug_data = login_menu_class::get_plugin_data($id);

        	$ret .= '
            	<tr>
            	<td class="forumheader3">'.LOGIN_MENU_L37.' '.LOGIN_MENU_L46.LOGIN_MENU_L45a." {$plug_data['eplug_name']} ".LOGIN_MENU_L45b.'<br /></td>
            	<td class="forumheader3">
                   <input type="checkbox" name="external_stats['.$id.']" value="1"'.(in_array($id, $enabled) ? ' checked="checked"' : '').' />
                </td>
            	</tr>
            ';
            $num++;
        }
        
        if($ret) {
            $ret = '<tr><td colspan="2" class="fcaption">'.LOGIN_MENU_L47.'</td></tr>'.$ret;
        }
        
        return $ret;
    }
    
    function get_stats_total() {
        global $menu_pref;
        
        $lbox_infos = login_menu_class::parse_external_list(true, false);
        if(!varsettrue($lbox_infos['stats'])) 
            return 0;
            
        $ret = 0;
        $lbox_active_sorted = $menu_pref['login_menu']['external_stats'] ? explode(',', $menu_pref['login_menu']['external_stats']) : array();
        
        foreach ($lbox_active_sorted as $stackid) { 
            if(!varset($lbox_infos['stats'][$stackid])) 
                continue;
            foreach ($lbox_infos['stats'][$stackid] as $lbox_item) {
                if($lbox_item['stat_new'])
                    $ret += $lbox_item['stat_new'];
            }
        }
        
        return $ret;
    }
    
    function get_plugin_data($plugid) {
        
        if(($tmp = getcachedvars('loginbox_eplug_data_'.$plugid)) !== FALSE) return $tmp;

        $ret = array();
        if(is_readable(e_PLUGIN.$plugid.'/plugin.php')) {
            
            include(e_PLUGIN.$plugid.'/plugin.php');
            $ret['eplug_name'] = defined($eplug_name) ? constant($eplug_name) : $eplug_name;
            $ret['eplug_version'] = $eplug_version;
            cachevars('loginbox_eplug_data_'.$plugid, $ret);
        }
        
        return $ret;
    }
    
    
    function clean_links($link_items) {
    
        if(empty($link_items)) return;
    
        foreach($link_items as $key => $value) {
            if(!varsettrue($value['link_url'])) {
                unset($link_items[$key]);
            }
        }
        
        return $link_items;
    }

}

?>