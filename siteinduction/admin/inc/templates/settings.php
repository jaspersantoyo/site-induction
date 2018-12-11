<div class="site-induction wrap">
  <h1>Intellicentre Induction Settings</h1>
  <?= $message; ?>
  <form method="post" action="" enctype="multipart/form-data">
    <?= $this->getGeneralSettings(); ?>
    <hr/>
    <?= $this->getEntryFormSettings(); ?>
    <hr/>
    <?= $this->getDownloadPrintSettings(); ?>
    <hr/>
    <?= $this->getSummaryPageSettings(); ?>
    <p class="submit">
      <input class="button-primary" type="submit" name="save_data" value="Save Settings" />
    </p>
  </form>
</div>