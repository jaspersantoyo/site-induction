<section id="download-print-settings">
  <h3>Download and Print Settings</h3>
  <table class="form-table">
    <tbody>
      <tr>
        <th>Heading : </th>
        <td><input type="text" name="download_print_heading" value="<?= $download_print_heading; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Content : </th>
        <td><textarea name="download_print_content" class="form-control"><?= $download_print_content; ?></textarea></td>
      </tr>
      <tr>
        <th>Button Label : </th>
        <td><input type="text" name="download_print_button_label" value="<?= $download_print_button_label; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Intellicentre Access Form : </th>
        <td>
          <input type="text" name="intellicentre_access_form" class="regular-text file-upload-url" value="<?= $intellicentre_access_form; ?>"/>
          <input type="button" class="button-secondary file-upload-btn"
            data-title="Upload Intellicentre Access Form" value="Upload Form" />
        </td>
      </tr>
    </tbody>
  </table>
</section>