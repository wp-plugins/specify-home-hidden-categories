<?php
/*
Plugin Name: Specify Home Hidden Categories
Description: Specify the Homepage&RSS hidden under all the article
Plugin URI: http://www.9sep.org/specify-home-hidden-categories
Version: 0.2.0
Author: Zhys
Author URI: http://www.9sep.org/author/zhys
License: GPLv2 or later

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined( 'ABSPATH' ) OR exit;
add_action( 'admin_init', 'specify_homepage_cats_init' );
add_filter('pre_get_posts', 'specifycats');

function specify_homepage_cats_init(){
	add_settings_field(
		'specify_cats',
		'Specify Categories',
		'specify_homepage_cats_callback_function',
		'reading',
		'default'
	);

	register_setting( 'reading', 'specify_cats' );
}

function specify_homepage_cats_callback_function(){
    $options    = get_option('specify_cats');
    $pag        = 'specify_cats';
    $_cats      = get_terms( 'category' );
    $html       = '';

    foreach ($_cats as $term){
    	if($options != ''){
    		$checked = in_array($term->term_id, $options) ? 'checked="checked"' : '';
    	}

    	$html .= '<p class="sep9_l">';
        $html .= sprintf( '<input type="checkbox" id="%1$s[%2$s]" name="%1$s[]" value="%2$s" %3$s />', $pag, $term->term_id, $checked );
        $html .= sprintf( '<label for="%1$s[%3$s]"> %2$s</label><br>', $pag, $term->name, $term->term_id );
        $html .= '</p>';
    }

    $html .= '<p class="sep"></p>';

    echo $html;
}

function getchild($id){
	$result = explode('/',get_category_children($id));
	$childs = array();
	foreach($result as $i){
		if(!empty($i))$childs[] = get_category($i);
	}
	return $childs;
}

function array_get_by_key(array $array, $string){
    if (!trim($string)) return false;
    preg_match_all("/\"$string\";\w{1}:(?:\d+:|)(.*?);/", serialize($array), $res);
    return $res[1];
}

function hidecats($catID){
	$cata=array();
	foreach($catID as $i){
		if(!empty($i))$cata[] = get_category($i);
		if(get_category_children($i)!=""){
			$cata[]=getchild($i);
		}else{
			$cata[]=$i;
		}
	}
	return array_unique(array_get_by_key($cata,'term_id'));
}

function specifycats($query){
	if ( $query->is_home || $query->is_feed ) {
		$query->set('category__not_in', hidecats(get_option('specify_cats')));
	}
	return $query;
}


