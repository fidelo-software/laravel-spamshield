<?php

beforeEach(function () {
	$uid = \Illuminate\Support\Str::random(10);
	$this->form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect(
		getUid: fn () => $uid
	);
	$this->request = new \Illuminate\Http\Request();
});

test('Timestamp - Detect true', function () {
	$begin = time();

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => $begin
	);
	$strategy = new \FideloSoftware\Spam\Strategies\TimestampStrategy($store, 2);

	$detected = $strategy->detect($this->form, $this->request);

	// too fast - not exceeding the minimum seconds
	$this->assertTrue($detected);
});

test('Timestamp - Detect false', function () {
	$seconds = 1;
	$begin = time();

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => $begin
	);
	$strategy = new \FideloSoftware\Spam\Strategies\TimestampStrategy($store, $seconds);

	sleep($seconds);

	$detected = $strategy->detect($this->form, $this->request);

	// Everything is fine - request was made after the given minimum seconds
	$this->assertFalse($detected);
});

test('Timestamp - Detect false (no store value)', function () {

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => null
	);
	$strategy = new \FideloSoftware\Spam\Strategies\TimestampStrategy($store, 2);
	$detected = $strategy->detect($this->form, $this->request);

	// No value in store - strategy should not detect spam
	$this->assertFalse($detected);
});