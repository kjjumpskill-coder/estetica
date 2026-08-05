<?php
/**
 * Головна сторінка. Порядок секцій — це і є структура лендінгу:
 * від обіцянки до доказів, від доказів до дії.
 *
 * Секція «Інтерв'ю» стоїть одразу після «Про майстра»: історія майстра
 * має підкріплюватися зовнішнім підтвердженням, поки читачка ще на ній.
 */
?>
<?php $this->section('hero') ?>
<?php $this->section('trust') ?>
<?php $this->section('about') ?>
<?php $this->section('interview') ?>
<?php $this->section('services') ?>
<?php $this->section('works') ?>
<?php $this->section('process') ?>
<?php $this->section('safety') ?>
<?php $this->section('diplomas') ?>
<?php $this->section('reviews') ?>
<?php $this->section('faq') ?>
<?php $this->section('gift') ?>
<?php $this->section('locations') ?>
<?php $this->section('booking') ?>
<?php $this->section('blog') ?>

<a class="btn btn--primary float-cta" href="#zapys" data-float-cta data-event="cta_float">Записатись</a>

<?php $this->partial('schema') ?>
