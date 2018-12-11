<section id="entry-form-settings">
  <h3>Entry Form Settings</h3>
  <table class="form-table">
    <tbody>
      <tr>
        <th>Entry Form Preheader : </th>
        <td><input type="text" name="entry_form_preheader" value="<?= $entry_form_preheader; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Entry Form Heading : </th>
        <td><input type="text" name="entry_form_heading" value="<?= $entry_form_heading; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Entry Form Sub-Heading : </th>
        <td><?= wp_editor($entry_form_subheading, 'entry_form_subheading', $editor); ?></td>
      </tr>
      <tr>
        <th>Entry Form Background : </th>
        <td>
          <input type="text" name="entry_form_background_image" class="regular-text file-upload-url" value="<?= $entry_form_background_image; ?>"/>
          <input type="button" class="button-secondary file-upload-btn" 
            data-title="Upload Entry Form Background" value="Upload Image" />
        </td>
      </tr>
    </tbody>
  </table>
</section>