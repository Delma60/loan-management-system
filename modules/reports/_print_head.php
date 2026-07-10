<?php
/**
 * Print-only report header. Expects $reportTitle (string).
 * Hidden on screen (see .print-title in style.css), shown when printing.
 */
?>
<div class="print-title" style="margin-bottom:16px;">
    <h1 style="font-family:var(--font-display);font-size:22px;margin:0;"><?php echo htmlspecialchars(APP_NAME); ?></h1>
    <p style="margin:2px 0 0;font-weight:600;"><?php echo htmlspecialchars($reportTitle ?? 'Report'); ?></p>
    <p style="margin:0;font-size:12px;color:#555;">Generated <?php echo date('d M Y, H:i'); ?></p>
</div>
