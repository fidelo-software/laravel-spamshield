<?php

beforeEach(function () {
	$this->form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect();
});

test('Honeypot - Html', function () {
	$fieldsCount = 3;

	$honeypot = new \FideloSoftware\Spam\Strategies\HoneypotStrategy($fieldsCount);
	$html = $honeypot->html($this->form);

	$matches = [];
	preg_match_all('/\<input(.*)name="(.*)"(.*)\/>/U', $html, $matches);

	expect($matches[2])->toHaveCount($fieldsCount);

	$this->assertEmpty(array_diff($matches[2], $honeypot->getAttributePool()));
});

test('Honeypot - Detect true', function () {
	$honeypot = new \FideloSoftware\Spam\Strategies\HoneypotStrategy(3);
	$attrPool = $honeypot->getAttributePool();
	$field = $attrPool[array_rand($attrPool)];

	$request = new \Illuminate\Http\Request();
	$request->query->add([$field => 'test', 'no_honey' => 'test']);

	$detected = $honeypot->detect($this->form, $request);

	$this->assertTrue($detected);
});

test('Honeypot - Detect false (empty)', function () {
	$honeypot = new \FideloSoftware\Spam\Strategies\HoneypotStrategy(3);
	$attrPool = $honeypot->getAttributePool();
	$field = $attrPool[array_rand($attrPool)];

	$request = new \Illuminate\Http\Request();
	$request->query->add([$field => '', 'no_honey' => 'test']);

	$detected = $honeypot->detect($this->form, $request);

	$this->assertFalse($detected);
});

test('Honeypot - Detect false (not existing)', function () {
	$honeypot = new \FideloSoftware\Spam\Strategies\HoneypotStrategy(3);

	$request = new \Illuminate\Http\Request();
	$request->query->add(['no_honey' => 'test']);

	$detected = $honeypot->detect($this->form, $request);

	$this->assertFalse($detected);
});