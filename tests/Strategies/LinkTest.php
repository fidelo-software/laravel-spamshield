<?php

beforeEach(function () {
	$this->request = new \Illuminate\Http\Request();
});

test('Link - Detect false (no link used)', function () {

	$form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getFieldValues: fn () => ['name' => 'John Smith']
	);

	$strategy = new \FideloSoftware\Spam\Strategies\LinkStrategy(0, ['name']);
	$detected = $strategy->detect($form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertFalse($detected);
});

test('Link - Detect true (link used - no links allowed)', function () {

	$form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getFieldValues: fn () => ['name' => 'https://fidelo.com']
	);

	$strategy = new \FideloSoftware\Spam\Strategies\LinkStrategy(0, ['name']);
	$detected = $strategy->detect($form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertTrue($detected);
});

test('Link - Detect false (link used - one link allowed)', function () {

	$form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getFieldValues: fn () => ['comment' => 'https://fidelo.com']
	);

	$strategy = new \FideloSoftware\Spam\Strategies\LinkStrategy(1, ['name']);
	$detected = $strategy->detect($form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertFalse($detected);
});

test('Link - Detect false (link used multiple fields - one link allowed)', function () {

	$form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getFieldValues: fn () => ['name' => 'https://fidelo.com', 'comment' => 'https://fidelo.com']
	);

	$strategy = new \FideloSoftware\Spam\Strategies\LinkStrategy(1, ['name']);
	$detected = $strategy->detect($form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertFalse($detected);
});

test('Link - Detect true (link used - one link allowed)', function () {

	$form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getFieldValues: fn () => ['name' => 'https://fidelo.com', 'comment' => 'https://fidelo.com']
	);

	$strategy = new \FideloSoftware\Spam\Strategies\LinkStrategy(1, ['*']);
	$detected = $strategy->detect($form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertTrue($detected);
});