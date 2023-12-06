
<section class="py-5 text-center container">
  <div class="row py-lg-5">
    <div class="col-lg-8 col-md-10 mx-auto">
      <table class="table text-start table-striped">
        <tr>
          <td>Full Name</td>
          <td></td>
          <td>Date Filed</td>
          <td></td>
        </tr>
        <tr>
          <td>CSID No.</td>
          <td></td>
          <td>Training Date</td>
          <td></td>
        </tr>
        <tr>
          <td>Designation</td>
          <td></td>
          <td>Start Date</td>
          <td></td>
        </tr>
        <tr>
          <td>Account's Name</td>
          <td></td>
          <td>Last Training Date</td>
          <td></td>
        </tr>
        <tr>
          <td>Department</td>
          <td></td>
          <td>End Date</td>
          <td></td>
        </tr>
      </table>
      <h4>EMPLOYEE CLEARANCE FORM</h4>
      <?= $this->Form->create(); ?>
      <table class="table table-bordered">
        <tbody>
          <?php foreach($forms as $department): ?>
            <tr>
              <th><?= $department['name'] ?></th>
              <th>Done</th>
              <th>Remarks</th>
              <th>Date</th>
              <th>Assessed By</th>
            </tr>
            <?php foreach($department['forms'] as $form): ?>
              <?php if($form['is_parent']): ?>
                <tr>
                  <td><?= $form['name'] ?></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
                <?php foreach($form['sub_forms'] as $sub_form): ?>
                  <tr>
                    <td><?= $sub_form['name'] ?></td>
                    <td>
                      <input <?= $sub_form['submitted_form']['subform_id'] == $sub_form['id'] ? 'checked' : '' ?>  name="subform[<?= $sub_form['id'] ?>][id]" class="form-check-input" type="checkbox" value="<?= $sub_form['id'] ?>" />
                      <input type="hidden" name="subform[<?= $sub_form['id'] ?>][parent_id]" value="<?= $form['id'] ?>" />
                    </td>
                    <td>
                      <textarea name="subform[<?= $sub_form['id'] ?>][remarks]" class="form-control" rows="1"><?= $sub_form['submitted_form']['subform_id'] == $sub_form['id'] ? $sub_form['submitted_form']['remarks'] : '' ?></textarea>
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td><?= $form['name'] ?></td>
                  <td>
                    <input <?= $form['submitted_form']['form_id'] == $form['id'] ? 'checked' : '' ?> name="form[<?= $form['id'] ?>][id]" class="form-check-input" type="checkbox" value="<?= $form['id'] ?>" />
                  </td>
                  <td>
                    <textarea name="form[<?= $form['id'] ?>][remarks]" class="form-control" rows="1"><?= $form['submitted_form']['remarks'] ?></textarea>
                  </td>
                  <td></td>
                  <td></td>
                </tr>
              <?php endif; ?>
            <?php endforeach ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="text-end">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
      <?= $this->Form->end(); ?>
    </div>
  </div>
</section>
