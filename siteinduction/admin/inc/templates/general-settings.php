<section id="general-settings">
  <h3>General Settings</h3>
  <table class="form-table">
    <tbody>
      <tr>
        <th>Heading : </th>
        <td><input type="text" name="plugin_heading" value="<?= $plugin_heading; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Sub-Heading : </th>
        <td><?= wp_editor($plugin_subheading, 'plugin_subheading', $editor); ?></td>
      </tr>
      <tr>
        <th>Banner Image : </th>
        <td>
          <input type="text" name="plugin_image_banner" class="regular-text file-upload-url" value="<?= $plugin_image_banner; ?>"/>
          <input type="button" class="button-secondary file-upload-btn" 
            data-title="Upload Banner Image" value="Upload Image" />
        </td>
      </tr>
      <tr>
        <th>Macquarie View App ID : </th>
        <td><input type="text" name="macquarie_view_app_id" value="<?= $macquarie_view_app_id; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Macquarie View App Secret : </th>
        <td><input type="text" name="macquarie_view_app_secret" value="<?= $macquarie_view_app_secret; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
      <th>Email Logo Image : </th>
        <td>
          <input type="text" name="plugin_email_logo" class="regular-text file-upload-url" value="<?= $plugin_email_logo; ?>"/>
          <input type="button" class="button-secondary file-upload-btn" 
            data-title="Upload Email Logo Image" value="Upload Image" />
        </td>
      </tr>
      <tr>
    </tbody>
  </table>
</section>