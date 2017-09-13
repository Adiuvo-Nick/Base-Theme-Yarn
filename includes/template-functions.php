<?php

/**
 * Main instance of KingInternational.
 *
 * Returns the main instance of BK to prevent the need to use globals.
 *
 * @return BaseTheme\BaseTheme
 */
function KI()
{
    return \BaseTheme\BaseTheme::instance();
}