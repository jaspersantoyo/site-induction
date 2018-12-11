<div class="site-induction invitation-view wrap">
  <h1 class="title">Invitation View
    <?php if(empty($data['date_completed'])) {?>
      <form method="post" action="" enctype="multipart/form-data" class="form-floater">
      <input class="add-new-h2" type="submit" name="resend_invitation" value="Resend Invitation" id="dci-invitation-resend"/>
    </form>
    <?php }?>
    <a class="add-new-h2 send-invitation" href="<?= get_admin_url(get_current_blog_id(), 'edit.php?post_type=intellicentre&page=email-invitation');?>"> New Invitation</a>
  </h1>
  <hr />
  <?= $message; ?>
  <table class="invitation-view-table">
    <tbody>
      <!-- TODO: seperate HTML and PHP -->
      <tr>
        <td class="first-column"><strong>First Name:</strong></td>
        <td><?= $data['customer_firstname']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Last Name:</strong></td>
        <td><?= $data['customer_lastname']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Company:</strong></td>
        <td><?= $data['customer_company']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Contracted to Company:</strong></td>
        <td><?= $data['customer_contracted_to_company']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Facility Servicing Contractor:</strong></td>
        <td><?= $data['facility_servicing_contractor']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Customer Email:</strong></td>
        <td><?= $data['customer_email']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Invitation Date Sent:</strong></td>
        <td><?= $data['date_sent']; ?></td>
      </tr>
      <tr>
        <td class="first-column"><strong>Quiz Completed:</strong></td>
        <td><?= $data['date_completed']; ?></td>
      </tr>
      <tr>
        <td class="first-column" valign="top"><strong>Invitation Message:</strong></td>
        <td  style="table-invitation-column"> <?= $body ?></td>
      </tr>
      <tr>
        <td><strong>Site Induction Link:</strong></td>
        <td><a href="<?= $data['site_induction_link_url']; ?>"><?= $data['site_induction_link_url']; ?></a></td>
      </tr>
    </tbody>
  </table>
  <div class="booking-details <?= $data['hide']; ?>">
    <strong>Data Center Intellicentre Booking:</strong>
    <ul>
      <?php
        $array =  explode(',', $data['location']);
        foreach ($array as $item) {
          echo "<li>$item</li>";
        }
      ?>
    </ul>
  </div>
  <script>
          document.getElementById('dci-invitation-resend').addEventListener('click', function(e) {
            e = e || window.event;
            e.currentTarget.value = "Resending Invitation...";
        },false);
  </script>
</div>