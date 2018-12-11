<section id="summary-page-settings">
  <h3>Summary Page Settings</h3>
  <table class="form-table">
    <tbody>
      <tr>
        <th>Summary Page Heading : </th>
        <td><input type="text" name="summary_page_heading" value="<?= $summary_page_heading; ?>" class="regular-text form-control" /></td>
      </tr>
      <tr>
        <th>Summary Page Success Notification Message  : </th>
        <td><textarea name="summary_page_success_message" class="form-control"><?= $summary_page_success_message; ?></textarea></td>
      </tr>
      <tr>
        <th>Summary Email Instruction Text : </th>
        <td><?= wp_editor($summary_email_instruction_text, 'summary_email_instruction_text', $editor); ?></td>
      </tr>
      <tr>
        <th>Finish Button Link : </th>
        <td>
          <fieldgroup>
            <input type="radio" name="callback_page_url_type" value="internal" <?= ($callback_page_url_type == 'internal') ? 'checked' : '' ; ?> /> Internal URL
            <span class="spacer"></span>
            <input type="radio" name="callback_page_url_type" value="external" <?= ($callback_page_url_type == 'external') ? 'checked' : '' ; ?> /> External URL
          </fieldgroup>
          <br/><br/>
          <select id="callback_page_internal_url" class="form-control form-select hidden">
            <?php foreach ($pages as $page) : ?>
              <option value="<?= $page->guid; ?>" <?= ($callback_page == $page->guid) ? 'selected' : '' ; ?> ><?= $page->post_title; ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" id="callback_page_external_url" value="<?= $callback_page; ?>" class="form-control hidden" />
          <input type="hidden" id="callback_page" name="callback_page" value="<?= $callback_page; ?>" />
        </td>
      </tr>
    </tbody>
  </table>
</section>