<?php
defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

if (empty($observations)) {
    echo '<p>' . JText::_('MOD_INATURALIST_OBSERVATIONS_NO_RESULTS') . '</p>';
    return;
}
?>

<ul class="inat-observations">
    <?php foreach ($observations as $obs): ?>
        <li class="inat-observation">
            <?php if (!empty($obs['photos'][0]['url'])): ?>
                <img src="<?php echo htmlspecialchars(str_replace('square', 'medium', $obs['photos'][0]['url'])); ?>" alt="">
            <?php endif; ?>
            <p>
                <strong><?php echo htmlspecialchars($obs['species_guess'] ?? $obs['taxon']['name'] ?? ''); ?></strong><br>
                <a href="<?php echo htmlspecialchars($obs['uri']); ?>" target="_blank" rel="noopener">
                    <?php echo JText::_('MOD_INATURALIST_OBSERVATIONS_VIEW_ON_INATURALIST'); ?>
                </a>
            </p>
        </li>
    <?php endforeach; ?>
</ul>
