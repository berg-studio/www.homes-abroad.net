<?php
/**
 * Template Name: Property Export
 *
 */
 //error_reporting(1);
global $wpdb;
/* checking and defining requested parameters */
if(isset($_REQUEST['kyero-xml'])){
	$kyero= true;
	$format="xml";
	if(isset($_REQUEST['limit'])) {
		$limit = $_REQUEST['limit'];
	}
}
if(isset($_REQUEST['xml2u-xml'])){
	$xml2u= true;
	$format="xml";
	if(isset($_REQUEST['limit'])) {
		$limit = $_REQUEST['limit'];
	}
}
else{
	if(empty($format)) {
		if(isset($_GET['format']))
			$format = $_GET['format'];
		else
			$format = "json";
	}
	if(isset($_REQUEST['limit'])) {
		$limit = $_REQUEST['limit'];
	}
	if(isset($_REQUEST['city'])) {
		$city_param = $_REQUEST['city'];
	}
	if(isset($_REQUEST['country'])) {
		$country_param = $_REQUEST['country'];
	}
	if(isset($_REQUEST['max_price'])) {
		$max_price = $_REQUEST['max_price'];
	}
	if(isset($_REQUEST['min_price'])) {
		$min_price = $_REQUEST['min_price'];
	}
	if(isset($_REQUEST['property_type'])) {
		$property_type_param = $_REQUEST['property_type'];
	}
}

/* format declaration ( XML / JSON )*/
if(strtoupper($format) == "XML"){
	$xml_format =true;
}
else {
	$xml_format=false;
}

/* setting headers */
if($xml_format) {
	ob_clean();
	header('Content-type: text/xml; encoding=utf-8');
	header('Content-Disposition: inline; filename="wpp_xml_data.xml"');
} else {
	header('Content-type: application/json');
	header('Content-Disposition: inline; filename="wpp_xml_data.json"'); 
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
}

if(ITAS_DEV_CONSTANT == true) {
	/* Getting published properties */
	$property_ids = $wpdb->get_col("SELECT ID FROM itas_wpp_property where post_status ='publish' ORDER BY ID");
	
	/* Getting requested properties from published properties */
	if($kyero){
		
		$export_properties = $wpdb->get_col("SELECT post_id from itas_wpp_property_field WHERE kyero_feed='1' and post_id IN (".implode(',',$property_ids)." )");
		
	if($xml2u){
		
		//CODE BY IT-ALLY for different costas
		$costa_del_sol_east = $wpdb->get_col("SELECT ID FROM itas_wpp_property WHERE costa LIKE '%Costa del Sol - East%' and ID IN (".implode(',',$property_ids)." ) ORDER BY ID DESC LIMIT 0,1500");
		$costa_del_sol_west = $wpdb->get_col("SELECT ID FROM itas_wpp_property WHERE costa LIKE '%Costa del Sol - West%' and ID IN (".implode(',',$property_ids)." ) ORDER BY ID DESC LIMIT 0,1500");
		$costa_north = $wpdb->get_col("SELECT ID FROM itas_wpp_property WHERE costa LIKE '%Costa Blanca - North%' and ID IN (".implode(',',$property_ids)." ) ORDER BY ID DESC LIMIT 0,1500");
		$costa_south = $wpdb->get_col("SELECT ID FROM itas_wpp_property WHERE costa LIKE '%Costa Blanca - South%' and ID IN (".implode(',',$property_ids)." ) ORDER BY ID DESC LIMIT 0,1500");
		
		$export_properties = array_merge($costa_north, $costa_south, $costa_del_sol_east, $costa_del_sol_west);
		
		$export_properties = array_unique($export_properties);		
		//CODE BY IT-ALLY
	
	}
	}else {
		/* Preparing query according to parameters */
		$query ="SELECT DISTINCT ID from itas_wpp_property WHERE 1=1";
		if(!empty($city_param)) $query.=" AND city LIKE '%".$city_param."%'";
		if(!empty($country_param)) $query.=" AND country LIKE '%".$country_param."%'";
		if(!empty($max_price)) $query.=" AND price <= ".$max_price;
		if(!empty($min_price)) $query.=" AND price >= ".$min_price;
		if(!empty($property_type_param)) $query.=" AND property_type LIKE '%".$property_type_param."%'";
		$query .= " AND ID IN (".implode(',',$property_ids)." )";

		$export_properties = $wpdb->get_col($query);
	}
	
	$index=0;
	foreach($export_properties as $id){
		/* checking limit parameter */
		if(!empty($limit) || $index > 6000){
			if($index == intval($limit) || $index > 6000){
				break;
			}
		}
		
		$ID[$index]= $id;
			
			$property_reference[$index] = $wpdb->get_col("SELECT reference from itas_wpp_property WHERE ID={$id}");
			$property_type[$index] = $wpdb->get_col("SELECT property_type from itas_wpp_property WHERE ID={$id}");
			$price[$index] = $wpdb->get_col("SELECT price from itas_wpp_property WHERE ID={$id}");
			$country[$index] = $wpdb->get_col("SELECT country from itas_wpp_property WHERE ID={$id}");
			$state[$index] = $wpdb->get_col("SELECT state from itas_wpp_property WHERE ID={$id}");
			$currency[$index] = "&#8364;";
			$property_status[$index] = $wpdb->get_col("SELECT status from itas_wpp_property WHERE ID={$id}");
			$city[$index] = $wpdb->get_col("SELECT city from itas_wpp_property WHERE ID={$id}");
			$postalcode[$index] = $wpdb->get_col("SELECT postal_code from itas_wpp_property WHERE ID={$id}");
			$county[$index] = $wpdb->get_col("SELECT county from itas_wpp_property WHERE ID={$id}");
			$latitude[$index] = $wpdb->get_col("SELECT latitude from itas_wpp_property WHERE ID={$id}");
			$longitude[$index] = $wpdb->get_col("SELECT longitude from itas_wpp_property WHERE ID={$id}");
			$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='slideshow_images' and post_id='{$id}'");
			if($slideshow_images[$index][0] == ""){
				$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attachment_metadata' and post_id='{$featured_ID['ID']}'");
			}
			$plot_size[$index] = $wpdb->get_col("SELECT plot_size from itas_wpp_property_field WHERE post_id='{$id}'");
			$area[$index] = $wpdb->get_col("SELECT area from itas_wpp_property_field WHERE post_id='{$id}'");
			$bedrooms[$index] = $wpdb->get_col("SELECT bedrooms from itas_wpp_property WHERE ID={$id}");
			$bathrooms[$index] = $wpdb->get_col("SELECT bathrooms from itas_wpp_property WHERE ID={$id}");
			$address[$index] = $wpdb->get_col("SELECT formatted_address from itas_wpp_property WHERE ID={$id}");
			$town[$index] = $wpdb->get_col("SELECT city from itas_wpp_property WHERE ID={$id}");
			$haspool[$index] = $wpdb->get_col("SELECT swimming_pool from itas_wpp_property_field WHERE post_id='{$id}'");
			$hasparking[$index] = $wpdb->get_col("SELECT parking from itas_wpp_property_field WHERE post_id='{$id}'");
			$hasgarden[$index] = $wpdb->get_col("SELECT garden from itas_wpp_property_field WHERE post_id='{$id}'");
			$hasterrace[$index] = $wpdb->get_col("SELECT terrace from itas_wpp_property_field WHERE post_id='{$id}'");
			$hasgarage[$index] = $wpdb->get_col("SELECT has_garage from itas_wpp_property_field WHERE post_id='{$id}'");
		
		$index++;		
	}
}else {
	/* Getting published properties */
	$property_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} where post_status ='publish' and post_type='property' ORDER BY ID");

	/* Getting requested properties from published properties */
	if($kyero){
		$export_properties = $wpdb->get_col("SELECT post_id from {$wpdb->postmeta} WHERE meta_key='rss_costa_blanca_north' and meta_value='true' and post_id IN (".implode(',',$property_ids)." )");	
	}
	else {
		/* Preparing query according to parameters */
		$query ="SELECT DISTINCT post_id from {$wpdb->postmeta} WHERE meta_key NOT LIKE 'rss_costa_blanca_north'";
		if(!empty($city_param)) $query.=" AND meta_key='location' AND meta_value LIKE '%".$city_param."%'";
		if(!empty($country_param)) $query.=" AND meta_key='country' AND meta_value LIKE '%".$country_param."%'";
		if(!empty($max_price)) $query.=" AND meta_key='price' AND meta_value <= ".$max_price;
		if(!empty($min_price)) $query.=" AND meta_key='price' AND meta_value >= ".$min_price;
		if(!empty($property_type_param)) $query.=" AND meta_key='property_type' AND meta_value LIKE '%".$property_type_param."%'";
		$query .= " AND post_id IN (".implode(',',$property_ids)." )";

		$export_properties = $wpdb->get_col($query);
	}

	$index=0;

	foreach($export_properties as $id){
		/* checking limit parameter */
		if(!empty($limit) || $index > 499){
			if($index == intval($limit) || $index > 499){
				break;
			}
		}
	
		$ID[$index]= $id;
		$property_reference[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='reference' and post_id='{$id}'");
		if($kyero){
			$property_type[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='property_type' and post_id='{$id}'");
			$price[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='price' and post_id='{$id}'");
			$country[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='country' and post_id='{$id}'");
			$state[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='state' and post_id='{$id}'");
			$currency[$index] = "&#8364;";
			$property_status[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='property_status' and post_id='{$id}'");
			$city[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='city' and post_id='{$id}'");
			$postalcode[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='postal_code' and post_id='{$id}'");
			$county[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='county' and post_id='{$id}'");
			$latitude[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='latitude' and post_id='{$id}'");
			$longitude[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='longitude' and post_id='{$id}'");
			$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='slideshow_images' and post_id='{$id}'");
			if($slideshow_images[$index][0] == ""){
				$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attachment_metadata' and post_id='{$featured_ID['ID']}'");
			}
			$plot_size[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='plot_size' and post_id='{$id}'");
			$area[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='area' and post_id='{$id}'");
			$bedrooms[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='bedrooms' and post_id='{$id}'");
			$bathrooms[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='bathrooms' and post_id='{$id}'");
			$address[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='formatted_address' and post_id='{$id}'");
			$town[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='city' and post_id='{$id}'");
			$haspool[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='swimming_pool' and post_id='{$id}'");
			$hasparking[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='parking' and post_id='{$id}'");
			$hasgarden[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='garden' and post_id='{$id}'");
			$hasterrace[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='terrace' and post_id='{$id}'");
			$hasgarage[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='has_garage' and post_id='{$id}'");
		}
		else{
			$property_type[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='property_type' and post_id='{$id}'");
			$price[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='price' and post_id='{$id}'");
			$country[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='country' and post_id='{$id}'");
			$state[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='state' and post_id='{$id}'");
			$currency[$index] = "&#8364;";
			$property_status[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='property_status' and post_id='{$id}'");
			$city[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='city' and post_id='{$id}'");
			$postalcode[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='postal_code' and post_id='{$id}'");
			$county[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='county' and post_id='{$id}'");
			$latitude[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='latitude' and post_id='{$id}'");
			$longitude[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='longitude' and post_id='{$id}'");
			$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='slideshow_images' and post_id='{$id}'");
			if($slideshow_images[$index][0] == ""){
				$slideshow_images[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attachment_metadata' and post_id='{$featured_ID['ID']}'");
			}
			$plot_size[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='plot_size' and post_id='{$id}'");
			$area[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='area' and post_id='{$id}'");
			$bedrooms[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='bedrooms' and post_id='{$id}'");
			$bathrooms[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='bathrooms' and post_id='{$id}'");
			$address[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='formatted_address' and post_id='{$id}'");
			$town[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='city' and post_id='{$id}'");
			$haspool[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='swimming_pool' and post_id='{$id}'");
			$hasparking[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='parking' and post_id='{$id}'");
			$hasgarden[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='garden' and post_id='{$id}'");
			$hasterrace[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='terrace' and post_id='{$id}'");
			$hasgarage[$index] = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='has_garage' and post_id='{$id}'");
		}
		$index++;
	}
}

/* Preparing Properties Array */
$properties = array();

/* if kyero-xml is true */

for($i=0; $i<$index; $i++){
	$ix = $ID[$i];
	
	if($kyero){		
		$properties['kyero'] = array();
		$properties['kyero']['feed_version']="3";	
		$properties['property'.$ix]['id']=$property_reference[$i][0];
		$properties['property'.$ix]['date']=$property_reference[$i][0];
		$properties['property'.$ix]['ref']=$property_reference[$i][0];
		$properties['property'.$ix]['price']=$price[$i][0];
		$properties['property'.$ix]['currency']="EUR";
		$properties['property'.$ix]['price_freq']="sale";
		$properties['property'.$ix]['part_ownership']="0";
		$properties['property'.$ix]['leasehold']="0";
		$properties['property'.$ix]['new_build']="0";
		$properties['property'.$ix]['type']=$property_type[$i][0];
		$properties['property'.$ix]['town']=$city[$i][0];
		$properties['property'.$ix]['region']=$county[$i][0];
		$properties['property'.$ix]['location']['latitude']=$latitude[$i][0];
		$properties['property'.$ix]['location']['longitude']=$longitude[$i][0];
		$properties['property'.$ix]['location_detail']="";
		$properties['property'.$ix]['bedrooms']=$bedrooms[$i][0];
		$properties['property'.$ix]['bathrooms']=$bathrooms[$i][0];
		$properties['property'.$ix]['pool']=$haspool[$i][0];
		$properties['property'.$ix]['surface_area']['built']=$area[$i][0];
		$properties['property'.$ix]['surface_area']['plot']=$plot_size[$i][0];
		$properties['property'.$ix]['energy_rating']['consumption']="";
		$properties['property'.$ix]['energy_rating']['emissions']="";
		$properties['property'.$ix]['url']['en']="";
			
		/* Getting property description variable. If there are no description available, print standard description*/
		$text = $wpdb->get_row("SELECT post_title,post_content FROM {$wpdb->posts} WHERE ID='{$ID[$i]}'",ARRAY_A);
			
		$properties['property'.$ix]['desc']['en'] = str_replace(chr(13),'',$text['post_content']);
		$properties['property'.$ix]['desc']['en'] = str_replace('&nbsp;',' ',$properties['property'.$ix]['desc']['en']);
		$properties['property'.$ix]['desc']['en'] = "<![CDATA[".str_replace("&","&amp;", $properties['property'.$ix]['desc']['en'])."]]>";				
			
		/* images*/
		$images_ids = unserialize($slideshow_images[$i][0]);
		$img_count=0;
		$images = array();
		foreach($images_ids as $p_id){
			$single_image = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' and post_id='{$p_id}'");
			if(count($single_image)!=0){
				$type = $wpdb->get_col("SELECT post_mime_type from {$wpdb->posts} WHERE ID='{$p_id}'");
				if($img_count==20) break;
					
				/* this is the one*/
				if($single_image[0]!="")
					$images['image'.$img_count++]['image']=get_option('home')."/wp-content/uploads/".$single_image[0];
					
				else
					$images['images'.$img_count++]['image']=$single_image[0];
				}
			}
			if(count($images)!=0){
				$properties['property'.$ix]['images'] = $images;
			}
			else {
				$posts = $wpdb->get_results("SELECT guid, post_mime_type FROM {$wpdb->posts} WHERE post_parent='{$ID[$i]}' AND post_mime_type LIKE '%image%'",ARRAY_A);
				
				foreach($posts as $post){
					if($img_count==20) break;
					//$images['image'.$img_count]['image_type']= $post['post_mime_type'];
					$images['image'.$img_count++]['image']=$post['guid'];
				}
				$properties['property'.$ix]['images'] = $images;
		}	
	}
	
	if($xml2u){		
		$properties['orderName']="BB HOUSEFINDING, S.L.";
		$properties['fileFormat']="XML2U Default - © 2009-2014 XML2U.com. All rights reserved. This xml structure  may not be reproduced, displayed, modified or distributed without the express prior written permission of the copyright holder. For permission, contact copyright@xml2u.com";
	
		$properties['ClientDetails'] = array();
		$properties['ClientDetails']['clientName']="www.homes-abroad.net";
		$properties['ClientDetails']['clientContact']="Christian Boesen";
		$properties['ClientDetails']['clientContactEmail']="christian@homes-abroad.net";
		$properties['ClientDetails']['clientTelephone']="+34609977262";
		
		
		$properties['properties']['property'.$ix]= array();
		$properties['properties']['property'.$ix]= array();
		$properties['properties']['property'.$ix]['propertyid']=$property_reference[$i][0];
		$properties['properties']['property'.$ix]['lastUpdateDate']="";
		$properties['properties']['property'.$ix]['category']="Residential For Sale";
		
		$properties['properties']['property'.$ix]['Address'] = array();
		$properties['properties']['property'.$ix]['Address']['number']="";
		$properties['properties']['property'.$ix]['Address']['street']="";
		$properties['properties']['property'.$ix]['Address']['postcode']=$postalcode[$i][0];
		$properties['properties']['property'.$ix]['Address']['location']=$city[$i][0];
		$properties['properties']['property'.$ix]['Address']['subRegion']="";
		$properties['properties']['property'.$ix]['Address']['region']=$county[$i][0];
		$properties['properties']['property'.$ix]['Address']['country']="Spain";
		$properties['properties']['property'.$ix]['Address']['countryCodeISO3166-1-alpha2']="ES";
		$properties['properties']['property'.$ix]['Address']['countryCodeISO3166-1-numeric']="724";
		$properties['properties']['property'.$ix]['Address']['countryCodeISO3166-1-alpha3']="ESP";
		$properties['properties']['property'.$ix]['Address']['latitude']=$latitude[$i][0];
		$properties['properties']['property'.$ix]['Address']['longitude']=$longitude[$i][0];
			
		$properties['properties']['property'.$ix]['Price'] = array();
		$properties['properties']['property'.$ix]['Price']['prefix']="";
		$properties['properties']['property'.$ix]['Price']['price']=$price[$i][0];
		$properties['properties']['property'.$ix]['Price']['currency']="EUR";
		$properties['properties']['property'.$ix]['Price']['frequency']="";
		$properties['properties']['property'.$ix]['Price']['avilableDate']="";
		$properties['properties']['property'.$ix]['Price']['status']="";
		$properties['properties']['property'.$ix]['Price']['reference']=$property_reference[$i][0];
		$properties['properties']['property'.$ix]['Price']['MlsId']="";
			
		$properties['properties']['property'.$ix]['Description'] = array();
		$properties['properties']['property'.$ix]['Description']['propertyType']=$property_type[$i][0];
		$properties['properties']['property'.$ix]['Description']['Tenure']="";
		$properties['properties']['property'.$ix]['Description']['tenanted']="";
		$properties['properties']['property'.$ix]['Description']['bedrooms']=$bedrooms[$i][0];
		$properties['properties']['property'.$ix]['Description']['bedroomRange']="";
		$properties['properties']['property'.$ix]['Description']['sleeps']="";
		$properties['properties']['property'.$ix]['Description']['fullBathrooms']=$bathrooms[$i][0];
		$properties['properties']['property'.$ix]['Description']['halfBathrooms']="";
		$properties['properties']['property'.$ix]['Description']['ensuites']="";
		$properties['properties']['property'.$ix]['Description']['rooms']="";
		$properties['properties']['property'.$ix]['Description']['receptionRooms']="";
		$properties['properties']['property'.$ix]['Description']['furnishings']="";
		$properties['properties']['property'.$ix]['Description']['title']="<![CDATA[".$property_type[$i][0]." ".$property_status[$i][0]." in ".str_replace('&ndash;','-',$city[$i][0]).", ".$county[$i][0]."]]>";
		/* Getting property description variable. If there are no description available, print standard description*/
		$text = $wpdb->get_row("SELECT post_title,post_content FROM {$wpdb->posts} WHERE ID='{$ID[$i]}'",ARRAY_A);
			
		$properties['property'.$ix]['desc']['en'] = str_replace(chr(13),'',$text['post_content']);
		$properties['property'.$ix]['desc']['en'] = str_replace('&nbsp;',' ',$properties['property'.$ix]['desc']['en']);
		$properties['property'.$ix]['desc']['en'] = "<![CDATA[".str_replace("&","&amp;", $properties['property'.$ix]['desc']['en'])."]]>";
			
		$properties['properties']['property'.$ix]['Description']['newBuild']="";
		$properties['properties']['property'.$ix]['Description']['yearBuild']="";
		$properties['properties']['property'.$ix]['Description']['numberOfFloors']="";
		$properties['properties']['property'.$ix]['Description']['floorNumber']="";
		$properties['properties']['property'.$ix]['Description']['condition']="";
		$properties['properties']['property'.$ix]['Description']['heating']="";
		$properties['properties']['property'.$ix]['Description']['elevator']="";
		$properties['properties']['property'.$ix]['Description']['fittedKitchen']="";
		$properties['properties']['property'.$ix]['Description']['assistedLiving']="";
		$properties['properties']['property'.$ix]['Description']['wheelchairFriendly']="";
		$properties['properties']['property'.$ix]['Description']['balcony']="";
		$properties['properties']['property'.$ix]['Description']['terrace']=$hasterrace[$i][0];
		$properties['properties']['property'.$ix]['Description']['swimmingPool']=$haspool[$i][0];
		$properties['properties']['property'.$ix]['Description']['orientation']="";
		$properties['properties']['property'.$ix]['Description']['garages']=$hasgarage[$i][0];
		$properties['properties']['property'.$ix]['Description']['offRoadParking']="";
		$properties['properties']['property'.$ix]['Description']['carports']="";
		$properties['properties']['property'.$ix]['Description']['openhouses']="";
		$properties['properties']['property'.$ix]['Description']['auctionTime']="";
		$properties['properties']['property'.$ix]['Description']['auctionPlace']="";
				
		$properties['properties']['property'.$ix]['Description']['FloorSize'] = array();
		$properties['properties']['property'.$ix]['Description']['FloorSize']['floorSize']=$area[$i][0];
		$properties['properties']['property'.$ix]['Description']['FloorSize']['floorSizeUnits']="sq meters";
				
		$properties['properties']['property'.$ix]['Description']['PlotSize'] = array();
		$properties['properties']['property'.$ix]['Description']['PlotSize']['plotSize']=$plot_size[$i][0];
		$properties['properties']['property'.$ix]['Description']['PlotSize']['plotSizeUnits']="sq meters";
			
		/* images*/
		$images_ids = unserialize($slideshow_images[$i][0]);
		$img_count=0;
		$images = array();
		foreach($images_ids as $p_id){
			$single_image = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' and post_id='{$p_id}'");
			if(count($single_image)!=0){
				$type = $wpdb->get_col("SELECT post_mime_type from {$wpdb->posts} WHERE ID='{$p_id}'");
				if($img_count==20) break;
					
				/* this is the one*/
				if($single_image[0]!="")
					$images['image'.$img_count++]['image']=get_option('home')."/wp-content/uploads/".$single_image[0];
				
				else
					$images['images'.$img_count++]['image']=$single_image[0];
			}
		}
		if(count($images)!=0){
				$properties['properties']['property'.$ix]['images'] = $images;
		}
		else {
			$posts = $wpdb->get_results("SELECT guid, post_mime_type FROM {$wpdb->posts} WHERE post_parent='{$ID[$i]}' AND post_mime_type LIKE '%image%'",ARRAY_A);
				
			foreach($posts as $post){
				if($img_count==20) break;
				$images['image'.$img_count]['image_type']= $post['post_mime_type'];
				$images['image'.$img_count++]['image']=$post['guid'];
			}
			$properties['properties']['property'.$ix]['images'] = $images;
		}	
	}
	
	else{		

		$properties['property'.$ix]['id']=$property_reference[$i][0];
		$properties['property'.$ix]['date']=$property_reference[$i][0];
		$properties['property'.$ix]['ref']=$property_reference[$i][0];
		$properties['property'.$ix]['price']=$price[$i][0];
		$properties['property'.$ix]['currency']="EUR";
		$properties['property'.$ix]['price_freq']="sale";
		$properties['property'.$ix]['part_ownership']="0";
		$properties['property'.$ix]['leasehold']="0";
		$properties['property'.$ix]['new_build']="0";
		$properties['property'.$ix]['type']=$property_type[$i][0];
		$properties['property'.$ix]['town']=$city[$i][0];
		$properties['property'.$ix]['region']=$county[$i][0];
		$properties['property'.$ix]['location']['latitude']=$latitude[$i][0];
		$properties['property'.$ix]['location']['longitude']=$longitude[$i][0];
		$properties['property'.$ix]['location_detail']="";
		$properties['property'.$ix]['bedrooms']=$bedrooms[$i][0];
		$properties['property'.$ix]['bathrooms']=$bathrooms[$i][0];
		$properties['property'.$ix]['pool']=$haspool[$i][0];
		$properties['property'.$ix]['surface_area']['built']=$area[$i][0];
		$properties['property'.$ix]['surface_area']['plot']=$plot_size[$i][0];
		$properties['property'.$ix]['energy_rating']['consumption']="";
		$properties['property'.$ix]['energy_rating']['emissions']="";
		$properties['property'.$ix]['url']['en']="";
			
		/* Getting property description variable. If there are no description available, print standard description*/
		$text = $wpdb->get_row("SELECT post_title,post_content FROM {$wpdb->posts} WHERE ID='{$ID[$i]}'",ARRAY_A);
			
		$properties['property'.$ix]['desc']['en'] = str_replace(chr(13),'',$text['post_content']);
		$properties['property'.$ix]['desc']['en'] = str_replace('&nbsp;',' ',$properties['property'.$ix]['desc']['en']);
		$properties['property'.$ix]['desc']['en'] = "<![CDATA[".str_replace("&","&amp;", $properties['property'.$ix]['desc']['en'])."]]>";				
			
		/* images*/
		$images_ids = unserialize($slideshow_images[$i][0]);
		$img_count=0;
		$images = array();
		foreach($images_ids as $p_id){
			$single_image = $wpdb->get_col("SELECT meta_value from {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' and post_id='{$p_id}'");
			if(count($single_image)!=0){
				$type = $wpdb->get_col("SELECT post_mime_type from {$wpdb->posts} WHERE ID='{$p_id}'");
				if($img_count==20) break;
					
				/* this is the one*/
				if($single_image[0]!="")
					$images['image'.$img_count++]['image']=get_option('home')."/wp-content/uploads/".$single_image[0];
					
				else
					$images['images'.$img_count++]['image']=$single_image[0];
				}
			}
			if(count($images)!=0){
				$properties['property'.$ix]['images'] = $images;
			}
			else {
				$posts = $wpdb->get_results("SELECT guid, post_mime_type FROM {$wpdb->posts} WHERE post_parent='{$ID[$i]}' AND post_mime_type LIKE '%image%'",ARRAY_A);
				
				foreach($posts as $post){
					if($img_count==20) break;
					//$images['image'.$img_count]['image_type']= $post['post_mime_type'];
					$images['image'.$img_count++]['image']=$post['guid'];
				}
				$properties['property'.$ix]['images'] = $images;
		}	
	}

}

$result = json_encode($properties);

$temper = 1;
if($xml_format) {  
	//$location = WP_CONTENT_DIR."/../export/xml_export.xml";
	//$fp = fopen($location,"w") or die("No Writing Permission");
	class ArrayToXML
	{
		/**
		 * The main function for converting to an XML document.
		 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
		 *
		 * @param array $data
		 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
		 * @param SimpleXMLElement $xml - should only be used recursively
		 * @return string XML
		 */
		 
		static $property_type_names =array("apartment","villa","plot","summer cottage");
		static $property_type_ids =array("1","2","3","4");
		
		public static function toXml($data, $rootNodeName = 'data', $xml=null)
		{
			// turn off compatibility mode as simple xml throws a wobbly if you don't.
			if (ini_get('zend.ze1_compatibility_mode') == 1)
			{
				ini_set ('zend.ze1_compatibility_mode', 0);
			}
		
			if ($xml == null)
			{	
				$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
			}
			
			// loop through the data passed in.
			foreach($data as $key => $value)
			{
				// no numeric keys in our xml
				if (is_numeric($key))
				{
					// make string key...
					$key = "object". (string) $key;
				}
				
				// replace anything not alpha numeric
				$key = preg_replace('/[^a-z|_]/i', '', $key);
				$key = strtolower($key);
				
				// if there is another array found recrusively call this function
				if (is_array($value))
				{
					if(strtolower($key) == "image"){						
						$node = $xml->addChild($key);
						$node->addAttribute('id', ++$temper);						
					}else {
						$node = $xml->addChild($key);
					}
					// recrusive call.
					ArrayToXML::toXml($value, $rootNodeName, $node);
				}
				else 
				{
					// add single node.
					//$value = htmlentities($value);
					$value = str_replace('&ndash;','',$value);
					
					
					/* if(strtolower($key) == "image"){
						$field_name = "1";
						$key = "field";
						$child = $xml->addChild($key,$value);
						$child->addAttribute('number', $field_name);
						
					}else { */
						$child = $xml->addChild($key,$value);
					// }
				}
				
			}
			// pass back as string. or simple xml object
			return $xml->asXML();
		}
	}
	
	$ArrayToXML = new ArrayToXML();
	
	$result = $ArrayToXML->toXml($properties, "root");
	
	// Coverting the CDATA to it's original form, i.e. without &lt; and &gt;
	$result = str_replace('&lt;![CDATA[','<![CDATA[',$result);
	$result = str_replace(']]&gt;',']]>',$result);
	
}

echo $result;
die();	
?>