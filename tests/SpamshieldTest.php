<?php

beforeEach(function () {
	$this->form = mock(\FideloSoftware\Spam\Contracts\Form::class)->expect();

	$this->strategy = mock(\FideloSoftware\Spam\Strategies\AbstractStrategy::class);
});

test('Banned false', function() {

	$maxAttempts = \FideloSoftware\Spam\SpamShield::MAX_ATTEMPTS;

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => ($maxAttempts - 1)
	);

	$request = mock(\Illuminate\Http\Request::class)->expect(
		ip: fn () => 'testing',
		exists: fn () => false
	);

	$banned = \FideloSoftware\Spam\SpamShield::isBanned($store, $request);

	$this->assertFalse($banned);
});

test('Banned true', function() {

	$maxAttempts = \FideloSoftware\Spam\SpamShield::MAX_ATTEMPTS;

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => ($maxAttempts + 1)
	);

	$request = mock(\Illuminate\Http\Request::class)->expect(
		ip: fn () => 'testing',
		exists: fn () => false
	);

	$banned = \FideloSoftware\Spam\SpamShield::isBanned($store, $request);

	$this->assertTrue($banned);
});

test('Banned exception', function() {

	$maxAttempts = \FideloSoftware\Spam\SpamShield::MAX_ATTEMPTS;

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => ($maxAttempts + 1),
		put: fn () => null
	);

	$request = mock(\Illuminate\Http\Request::class)->expect(
		ip: fn () => 'testing',
		exists: fn () => false
	);

	$spamShield = new \FideloSoftware\Spam\SpamShield([], $store);
	$spamShield->detect($this->form, $request);

})->throws(\FideloSoftware\Spam\Exceptions\BannedException::class);

test('Detect no spam', function() {

	expect(function () {
		$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
			get: fn () => 0
		);
		$request = mock(\Illuminate\Http\Request::class)->expect(
			ip: fn () => 'testing',
			exists: fn () => false
		);

		$strategy = $this->strategy->expect(detect: fn () => false, cleanUp: fn () => null);

		$spamShield = new \FideloSoftware\Spam\SpamShield([$strategy], $store);
		$spamShield->detect($this->form, $request);
	})->not->toThrow(\FideloSoftware\Spam\Exceptions\SpamDetectionException::class);

});

test('Detect spam', function() {

	$store = mock(\Illuminate\Contracts\Cache\Store::class)->expect(
		get: fn () => 0,
		put: fn () => null
	);
	$request = mock(\Illuminate\Http\Request::class)->expect(
		ip: fn () => 'testing',
		exists: fn () => false
	);

	$strategy = $this->strategy->expect(detect: fn () => true, cleanUp: fn () => null);

	$spamShield = new \FideloSoftware\Spam\SpamShield([$strategy], $store);
	$spamShield->detect($this->form, $request);

})->throws(\FideloSoftware\Spam\Exceptions\SpamDetectionException::class);;