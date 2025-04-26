<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_inaturalist_observations
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Falls keine Beobachtungen vorhanden sind
if (empty($observations)) {
    echo '<div class="alert alert-info" style="font-family: Inter, Roboto, \'Helvetica Neue\', \'Arial Nova\', \'Nimbus Sans\', Arial, sans-serif;">' . Text::_('MOD_INATURALIST_OBSERVATIONS_NO_RESULTS') . '</div>';
    return;
}
?>

<div class="row g-3" style="font-family: Inter, Roboto, 'Helvetica Neue', 'Arial Nova', 'Nimbus Sans', Arial, sans-serif;">
<?php foreach ($observations as $observation) :

    // Bild ermitteln
    $photoUrl = '';
    if (!empty($observation['photos'][0]['url'])) {
        $photoUrl = str_replace('square', 'medium', $observation['photos'][0]['url']);
    }

    // Name ermitteln
    $commonName = $observation['taxon']['preferred_common_name'] ?? '';
    $scientificName = $observation['taxon']['name'] ?? '';
    $displayName = $commonName ?: $scientificName;

    // Beobachtungsdatum formatieren
    $date = '';
    if (!empty($observation['observed_on'])) {
        $dateObject = new DateTime($observation['observed_on']);
        $date = $dateObject->format('d. F Y');
    }

    // Ort zusammensetzen
    $place = '';
    if (!empty($observation['place_guess'])) {
        $place = htmlspecialchars($observation['place_guess'], ENT_QUOTES, 'UTF-8');
    }

    // Link zur Detailseite
    $observationUrl = 'https://www.inaturalist.org/observations/' . (int) $observation['id'];

?>
    <div class="col-12">
        <div class="card h-100">
            <div class="row g-0">
                <?php if ($photoUrl) : ?>
                    <div class="col-auto">
                        <a href="<?php echo $observationUrl; ?>" target="_blank" rel="noopener">
                            <img src="<?php echo $photoUrl; ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>" style="width:100px; height:100px; object-fit:cover;">
                        </a>
                    </div>
                <?php endif; ?>

                <div class="col">
                    <div class="card-body py-2 px-3">
                        <h5 class="card-title mb-1" style="font-size: 1rem;">
                            <a href="<?php echo $observationUrl; ?>" target="_blank" rel="noopener" class="text-decoration-none text-dark">
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
        </div>
    </div>

<?php endforeach; ?>
</div>

<?php
// Benutzerbild + Link zu allen Beobachtungen
$userAvatar = $avatar ?? 'https://static.inaturalist.org/attachments/users/icons/XXXXX/thumb.jpg'; // Standardbild, falls kein Avatar vorhanden
$profileLink = 'https://www.inaturalist.org/observations/' . htmlspecialchars($userId);
?>

<div class="d-flex align-items-center mt-4" style="font-family: Inter, Roboto, 'Helvetica Neue', 'Arial Nova', 'Nimbus Sans', Arial, sans-serif;">
    <div class="flex-shrink-0">
        <a href="<?php echo $profileLink; ?>" target="_blank" rel="noopener">
            <img src="<?php echo $userAvatar; ?>" alt="iNaturalist Profile" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
        </a>
    </div>
    <div class="flex-grow-1 ms-3">
        <a href="<?php echo $profileLink; ?>" target="_blank" rel="noopener" class="fw-bold text-decoration-none text-dark">
            <?php echo Text::_('MOD_INATURALIST_OBSERVATIONS_VIEW_ON_INATURALIST'); ?>
        </a>
    </div>
</div>
