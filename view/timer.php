
<?php

$timerVals = $this->values['timer'];
// echo '<pre>'.print_r($timerVals, 1).'</pre>';

?>

<h1><?= $timerVals['name'] ?></h1>
<p class="small"><?= sprintf(_("Created %s"), $timerVals['created']) ?></p>
<p class="small"><?= sprintf(_("Timer resets to: %s days, %s hours %s minutes %s seconds"),
								$timerVals['time_between']['d'],
								$timerVals['time_between']['h'],
								$timerVals['time_between']['m'],
								$timerVals['time_between']['s'],
								) ?></p>

<p><?= sprintf(_("Description: %s"), $timerVals['description']) ?></p>

<div id="resetButton">
	<form method="post" id="resetTimerForm"><input type="hidden" name="resetTimer" value="reset"></form>
	<img src="view/images/orangeButton_640.png" onclick="document.getElementById('resetTimerForm').submit();" style="height: 284px;"/>
</div>

<div id="countdown" style="font-size: 300%;"><?= sprintf(_("%s seconds left"),$timerVals['seconds_left']) ?></div>

<script>
// Set the distance outsidde of setInterval, otherwise it resets every loop and nothing happens ;)
// Seconds left on page load:
var distance = <?= $timerVals['seconds_left'] ?>;

// Update the count down every 1 second
var x = setInterval(function() {
	// Don't forget to decrease counter! ;)
	distance -= 1;

	// Time calculations for days, hours, minutes and seconds
	var days = Math.floor(distance / (60 * 60 * 24));
	var hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
	var minutes = Math.floor((distance % (60 * 60)) / 60);
	var seconds = distance % 60;
	
  if (distance <= 0) {
	clearInterval(x);
	document.getElementById("countdown").innerHTML = "EXPIRED";
  }
  else
  {
	  // Display the result in the element with id="countdown"
	  document.getElementById("countdown").innerHTML = days + "d " + hours + "h "
	  + minutes + "m " + seconds + "s ";
  }
  // If the count down is finished, write some text
}, 1000);
</script>

<?php

if($timerVals['seconds_between']==NULL)
{
    echo '<p>Please enter how much time a reset adds</p>';
    $this->renderTimerForm($timerVals['name']);
}

?>