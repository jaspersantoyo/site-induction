<div class="site-induction wrap">
  <h1>Email Invitation</h1>
  <hr />
  <?= $message; ?>
  <form method="post" action="" enctype="multipart/form-data">
    <table class="form-table">
      <tbody>
        <!-- TODO: seperate HTML and PHP -->
        <tr>
          <th><label for="dci_invitation_firstname">Customer First Name : </label></th>
          <td><input id="dci_invitation_firstname" type="text" name="customer_firstname" value="<?= $data['customer_firstname']; ?>" class="regular-text form-control" required /></td>
        </tr>
        <tr>
          <th><label for="dci_invitation_lastname">Customer Last Name : </label></th>
          <td><input id="dci_invitation_lastname" type="text" name="customer_lastname" value="<?= $data['customer_lastname']; ?>" class="regular-text form-control" required /></td>
        </tr>
        <tr>
          <th><label for="dci_invitation_email">Customer Email : </label></th>
          <td><input id="dci_invitation_email" type="email" name="customer_email" value="<?= $data['customer_email']; ?>" class="regular-text form-control" required /></td>
        </tr>
        <tr>
          <th><label for="dci_invitation_facility_servicer">Facility Servicing Contractor : </label></th>
          <td><input id="dci_invitation_facility_servicer" type="checkbox" name="facility_servicing_contractor" value="Yes" <?php echo ($data['facility_servicing_contractor']=='Yes' ? 'checked' : '');?> /></td>
        </tr>
        <tr>
          <th>Invitation Message : </th>
          <td><?= wp_editor($data['invitation_message'], 'invitation_message', $editor); ?></td>
        </tr>
        <tr>
          <th><label for="dci_invitation_invitation_link_text">Site Induction Link Text : </label></th>
          <td>
            <input id="dci_invitation_invitation_link_text" type="text" name="site_induction_link_text" class="regular-text form-control" value="<?= $data['site_induction_link_text']; ?>" />
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input class="button-primary" type="submit" name="send_invitation" value="Send Invitation" />
    </p>
  </form>
</div>