<?php 
defined('EMONCMS_EXEC') or die('Restricted access');
include_once "model.php";

global $mysqli, $path;
$userId = 1; //Currently using only one user, but in the future there should be more. Database schema is ready for multiple users.
$data = get_all_data();
?>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
<script src="<?php echo $path; ?>Modules/pills/form.js"></script>
<link rel="stylesheet" href="<?php echo $path; ?>Modules/pills/style.css" />

<form method="post" action="">
<label for="weekNumber">Choose week starting on:</label>
<select name="weekNumber">
<?php
$daySecs = 60 * 60 * 24;
$weekSecs = $daySecs * 7;
$monday = time() - ((date("N") - 1) * $daySecs) - (date("G") * 3600) - (date("i") * 60) - date("s");
?>
<?php for($week = date("W"); $week < date("W") + 4; $week++): ?>
	<option value="<?php echo $monday; ?>"><?php echo date("jS F", $monday); ?></option>
	<?php $monday += $weekSecs; ?>
<?php endfor; ?>
</select>
<button id="copyFromOneToAll" type="button" class="btn">Copy last modified cell to all cells</button>
<button id="copyFromOneDayToAllDays" type="button" class="btn">Copy last modified day to all days</button>
<table class="table">
<?php
$daytimes = array("Morning", "Mid-day", "Tea Time", "Bedtime");
$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
$default_times = array("07:00", "12:00", "17:00", "22:00");
$input_index = 0;
?>
<thead>
<tr>
<th></th>
<?php foreach($daytimes as $time): ?>
<th><?php echo $time; ?></th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($days as $day): ?>
<tr>
	<th><?php echo $day; ?></th>
	<?php 
	for($i = 0; $i < 4; $i++): 
		$timeValue = $data ? date("H:i", $data[$input_index]["deadline"]) : $default_times[$i];
		$importance = $data ? $data[$input_index]["importance"] : 0;
		$names = $data ? $data[$input_index]["names"] : Array();
	?>
	<td>
			<div>
			<input type="time" class="form-control" value="<?php echo $timeValue; ?>" name="<?php echo $input_index."[time]"; ?>"/><br>
			<select class="form-control" name="<?php echo $input_index."[importance]"; ?>"><br>
				<?php $importances = ["Not Important", "Important", "Very Important"]; ?>
				<?php for($j = 0; $j < 3; $j++): ?>
					<option <?php if($j == $importance) echo "selected "; ?>value="<?php echo $j; ?>"><?php echo $importances[$j]; ?></option>
				<?php endfor; ?>
			</select>
			<?php foreach($names as $name): ?>
				<div>
					<div class="btn-group">
						<button type="button" class="btn btn-default pillNameButton"><?php echo $name; ?></button>
						<button type="button" class="btn btn-danger" data-id="<?php echo $name; ?>" onClick="removePillName(this);">X</button>
					</div>
					<br/><br/>
				</div>
				<input name="<?php echo $input_index; ?>[pillNames][]" type="hidden" value="<?php echo $name; ?>">
			<?php endforeach; ?>
			<input id="<?php echo $input_index; ?>" type="text" class="form-control pillNameInput" placeholder="Enter pill name" /><br>
			<button type="button" class="btn addPillButton">Add Pill</button>
			</div>
	</td>
	<?php $input_index++; ?>
	<?php endfor; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<button type="submit" class="btn btn-default">Submit</button>
