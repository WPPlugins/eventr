<?php
require_once('wpframe.php');
global $wpdb;
$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_home'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

$event = $wpdb->get_row($wpdb->prepare("SELECT ID,name,description FROM {$wpdb->prefix}eventr_event WHERE ID=%d", $event_id));

// Cache the options.
$options = array();
$plugin_options = array('bio_list', 'email_list', 'phone_list', 'image_list');
foreach($plugin_options as $opt) {
	$options[$opt] = get_option('eventr_' . $opt);
}

if($event) {
if(!isset($GLOBALS['eventr_attendee_client_includes_loaded'])) {
?>
<link type="text/css" rel="stylesheet" href="<?=$GLOBALS['wpframe_plugin_folder']?>/attendee.css" />
<?php
$GLOBALS['eventr_attendee_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
}

// Retrieve the Attendees
$sort = '';
if(isset($_REQUEST['sort']) and $_REQUEST['sort'] == 'name') $sort = 'A.name,';
$all_attendee = $wpdb->get_results($wpdb->prepare("SELECT A.ID, A.name, A.url,A.email,A.phone, A.description,A.picture, EA.added_on FROM `{$wpdb->prefix}eventr_attendee` AS A
				INNER JOIN `{$wpdb->prefix}eventr_event_attendee` AS EA ON attendee_ID=A.ID
				WHERE EA.event_ID=%d AND A.status='1' ORDER BY $sort EA.added_on", $event_id));
e('Total Attendees: ');
print count($all_attendee);

$colspan = 2;
$rowspan = ($options['bio_list']) ? 2 : 1;
?>
<table class="eventr-attendees">
<tr>
<th>#</th>
<th><a href="?sort=name"><?php e('Name'); ?></a></th>
<?php if($options['email_list']) { ?><th><?php e('Email'); ?></th><?php $colspan++; } ?>
<?php if($options['phone_list']) { ?><th><?php e('Phone'); ?></th><?php $colspan++; } ?>
<?php if($options['image_list']) { ?><th rowspan="<?php echo $rowspan ?>"><?php e('Image'); ?></th><?php } ?>
</tr>
<?php if($options['bio_list']) { ?><tr><th colspan="<?php echo $colspan ?>">Bio</th></tr><?php } ?>


<?php
if (count($all_attendee)) {
	$bgcolor = '';

	$attendee_count = 0;
	foreach($all_attendee as $attendee) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		$attendee_count++;
		print "<tr id='attendee-{$attendee->ID}' class='$class'>\n";
		
		?>
		<td class="count"><?php echo $attendee_count ?>.</td>
		<td class="description"><strong><?php
		if($attendee->url) print "<a href='{$attendee->url}'>" . stripslashes($attendee->name) . "</a>";
		else print stripslashes($attendee->name);
		?></strong></td>
		<?php if($options['email_list']) { ?><td><?php echo stripslashes($attendee->email); ?></td><?php } ?>
		<?php if($options['phone_list']) { ?><td><?php echo stripslashes($attendee->phone); ?></td><?php } ?>
		<?php if($options['image_list']) { ?><td rowspan="<?php echo $rowspan ?>"><?php if($attendee->picture) echo "<img src='{$attendee->picture}' alt='{$attendee->name}' width='150'  />"; ?></td><?php } ?>
		</tr>
		<?php if($options['bio_list']) { ?><tr class='<?php echo $class ?>'><td colspan="<?php 
			echo (!$attendee->picture) ? ($colspan+1) : $colspan ?>"><?php echo stripslashes($attendee->description); ?>&nbsp;</td></tr><?php } ?>
<?php
	}
} else {
?>
	<tr style='background-color: <?php echo $bgcolor; ?>;'>
		<td colspan="<?php echo $colspan+1 ?>"><?php e('No attendees found.') ?></td>
	</tr>
<?php
}
?>
</table>
<?php	

}