<?php
/**
 * Port of section-heading.tsx.
 *
 * NOTE: CodeIgniter persists view data across view() calls, so every call
 * site must pass eyebrow/subtitle/href/linkLabel/align explicitly (null/absent
 * values included) to avoid values leaking from a previous heading.
 */
$eyebrow   = $eyebrow ?? null;
$subtitle  = $subtitle ?? null;
$href      = $href ?? null;
$linkLabel = $linkLabel ?? 'View All';
$align     = $align ?? 'left';
?>
<div class="mb-8 flex flex-wrap items-end justify-between gap-4<?= $align === 'center' ? ' flex-col items-center text-center' : '' ?>">
    <div class="max-w-2xl<?= $align === 'center' ? ' flex flex-col items-center' : '' ?>">
        <?php if ($eyebrow): ?>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-brand-600"><?= esc($eyebrow) ?></p>
        <?php endif ?>
        <h2 class="text-3xl font-extrabold tracking-tight text-ink sm:text-4xl"><?= esc($title) ?></h2>
        <?php if ($subtitle): ?>
            <p class="mt-2 text-sm leading-relaxed text-ink-soft sm:text-base"><?= esc($subtitle) ?></p>
        <?php endif ?>
    </div>
    <?php if ($href): ?>
        <a href="<?= esc($href, 'attr') ?>" class="group inline-flex items-center gap-1.5 text-sm font-bold text-brand-700 transition-colors hover:text-brand-800">
            <?= esc($linkLabel) ?>
            <?= lucide('arrow-right', 'size-4 transition-transform group-hover:translate-x-1') ?>
        </a>
    <?php endif ?>
</div>
