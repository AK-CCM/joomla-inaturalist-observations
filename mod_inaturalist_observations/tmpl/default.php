<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_inaturalist_observations
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$observations = $data['observations'] ?? [];
$avatar = $data['avatar'] ?? '';
$username = $data['username'] ?? '';

if (empty($observations)) {
    echo '<div class="alert alert-info" style="font-family: Inter, Roboto, \'Helvetica Neue\', \'Arial Nova\', \'Nimbus Sans\', Arial, sans-serif;">' . Text::_('MOD_INATURALIST_OBSERVATIONS_NO_RESULTS') . '</div>';
    return;
}
?>

<div style="font-family: Inter, Roboto, 'Helvetica Neue', 'Arial Nova', 'Nimbus Sans', Arial, sans-serif;">
<?php foreach ($observations as $observation) :

    $photoUrl = !empty($observation['local_photo']) ? Uri::root() . $observation['local_photo'] : '';

    $commonName = $observation['taxon']['preferred_common_name'] ?? '';
    $scientificName = $observation['taxon']['name'] ?? '';
    $displayName = $commonName ?: $scientificName;

    $date = '';
    if (!empty($observation['observed_on'])) {
        $dateObject = new DateTime($observation['observed_on']);
        $date = $dateObject->format('d. F Y');
    }

    $place = !empty($observation['place_guess']) ? htmlspecialchars($observation['place_guess'], ENT_QUOTES, 'UTF-8') : '';

    $observationUrl = 'https://www.inaturalist.org/observations/' . (int) $observation['id'];
?>

    <div class="row g-0 align-items-start mb-2">
        <?php if ($photoUrl) : ?>
            <div class="col-auto">
                <a href="<?php echo $observationUrl; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>"
                         style="width:85px; height:85px; object-fit:cover;">
                </a>
            </div>
        <?php endif; ?>

        <div class="col ps-3">
            <div>
                <h5 class="mb-1 mt-0" style="font-size: 1rem;">
                    <a href="<?php echo $observationUrl; ?>" target="_blank" rel="noopener" class="text-decoration-none">
                        <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h5>
                <?php if ($date) : ?>
                    <div class="text-muted small"><?php echo $date; ?></div>
                <?php endif; ?>
                <?php if ($place) : ?>
                    <div class="text-muted small"><?php echo $place; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 1rem 0;">

<?php endforeach; ?>
</div>

<?php if ($avatar) : ?>
<div class="d-flex align-items-center mt-4" style="font-family: Inter, Roboto, 'Helvetica Neue', 'Arial Nova', 'Nimbus Sans', Arial, sans-serif;">
    <div class="flex-shrink-0">
        <a href="https://www.inaturalist.org/observations?user_id=<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <img src="<?php echo Uri::root() . $avatar; ?>" alt="iNaturalist Profile" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
        </a>
    </div>
    <div class="flex-grow-1 ms-3">
        <a href="https://www.inaturalist.org/observations?user_id=<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="fw-bold text-decoration-none">
            <?php echo Text::_('MOD_INATURALIST_OBSERVATIONS_VIEW_ON_INATURALIST'); ?>&nbsp;»
        </a>
    </div>
</div>
<?php endif; ?>
