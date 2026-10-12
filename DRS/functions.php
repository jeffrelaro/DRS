<?php
/**
 * Generates a random 4-digit OTP as a zero-padded string, e.g. "0042".
 */
function generate_otp(): string
{
    return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}
