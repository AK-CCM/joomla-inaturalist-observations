<div class="inat-observations" style="display: flex; flex-wrap: wrap; gap: 1rem;">
<?php if (!empty($observations)): ?>
    <?php foreach ($observations as $obs): ?>
        <div style="width: 180px; font-family: sans-serif;">
            <?php if (!empty($obs['photos'])): ?>
                <a href="<?php echo $obs['uri']; ?>" target="_blank" rel="noopener">
                    <img src="<?php echo str_replace('square', 'medium', $obs['photos'][0]['url']); ?>"
                         alt="<?php echo htmlspecialchars($obs['taxon']['preferred_common_name'] ?? $obs['taxon']['name']); ?>"
                         style="width: 100%; border-radius: 8px;">
                </a>
            <?php endif; ?>
            <p style="margin: 0.5em 0 0; font-size: 0.95em;">
                <strong><?php echo htmlspecialchars($obs['taxon']['preferred_common_name'] ?? $obs['taxon']['name']); ?></strong><br>
                <small><?php echo date('d.m.Y', strtotime($obs['observed_on'])); ?></small>
            </p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No observations found.</p>
<?php endif; ?>
</div>
