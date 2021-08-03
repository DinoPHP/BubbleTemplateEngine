<?php

include '../vendor/autoload.php';

// Create new Bubble instance
$templates = new Dinophp\Bubble\Engine('templates');

// Preassign data to the layout
$templates->addData(['company' => 'The Company Name'], 'layout');

// Render a template
echo $templates->render('profile', ['name' => 'Jonathan']);
