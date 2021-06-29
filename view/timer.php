
<?php

$timerVals = $this->values['timer'];
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

<?php

if($timerVals['seconds_between']==NULL)
{
    echo '<p>Please enter how much time a reset adds</p>';
    $this->renderTimerForm($timerVals['name']);
}

?>