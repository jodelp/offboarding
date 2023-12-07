
<section class="py-5 text-center container">
  <div class="row py-lg-5">
    <div class="col-lg-10 col-md-12 mx-auto">
      <table class="table text-start table-striped">
        <tr>
          <td>Full Name</td>
          <td><?= $staffInfo['first_name'] ?></td>
          <td>Date Filed</td>
          <td><?= date('Y-m-d', strtotime($staffInfo['created'])) ?></td>
        </tr>
        <tr>
          <td>CSID No.</td>
          <td><?= $staffInfo['employee_id'] ?></td>
          <td>Training Date</td>
          <td><?= $staffInfo['training_date'] ?></td>
        </tr>
        <tr>
          <td>Designation</td>
          <td><?= $staffInfo['designation'] ?></td>
          <td>Start Date</td>
          <td><?= $staffInfo['start_date'] ?></td>
        </tr>
        <tr>
          <td>Account's Name</td>
          <td><?= $staffInfo['client_name'] ?></td>
          <td>Last Training Date</td>
          <td><?= $staffInfo['training_date'] ?></td>
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
              <th><?= strtoupper($department['name']) ?></th>
              <th>Done</th>
              <th>Remarks</th>
              <th width="100">Date</th>
              <th>Assessed By</th>
            </tr>
            <?php foreach($department['forms'] as $form): ?>
              <?php if($form['is_parent'] == 'yes'): ?>
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
                      <input disabled <?= $sub_form['submitted_form']['subform_id'] == $sub_form['id'] ? 'checked' : '' ?>  name="subform[<?= $sub_form['id'] ?>][id]" class="form-check-input" type="checkbox" value="<?= $sub_form['id'] ?>" />
                      <input type="hidden" name="subform[<?= $sub_form['id'] ?>][parent_id]" value="<?= $form['id'] ?>" />
                    </td>
                    <td>
                      <textarea disabled name="subform[<?= $sub_form['id'] ?>][remarks]" class="form-control" rows="1"><?= $sub_form['submitted_form']['subform_id'] == $sub_form['id'] ? $sub_form['submitted_form']['remarks'] : '' ?></textarea>
                    </td>
                    <td><?= $sub_form['submitted_form']['subform_id'] == $sub_form['id'] ? date('Y-m-d', strtotime($sub_form['submitted_form']['created'])) : '' ?></td>
                    <td><?= $sub_form['submitted_form']['poc']['poc_email'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td><?= $form['name'] ?></td>
                  <td>
                    <input disabled <?= $form['submitted_form']['form_id'] == $form['id'] ? 'checked' : '' ?> name="form[<?= $form['id'] ?>][id]" class="form-check-input" type="checkbox" value="<?= $form['id'] ?>" />
                  </td>
                  <td>
                    <textarea disabled name="form[<?= $form['id'] ?>][remarks]" class="form-control" rows="1"><?= $form['submitted_form']['remarks'] ?></textarea>
                  </td>
                  <td><?= $form['submitted_form']['form_id'] == $form['id'] ? date('Y-m-d', strtotime($form['submitted_form']['created'])) : '' ?></td>
                  <td><?= $form['submitted_form']['poc']['poc_email'] ?></td>
                </tr>
              <?php endif; ?>
            <?php endforeach ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="text-end">
        <?= 
          $this->Html->link(
            'Back',
            '/forms/list',
            ['class' => 'btn btn-secondary']
          );
        ?>
      </div>
      <?= $this->Form->end(); ?>
      <?php if($staffInfo['role'] === 'admin'): ?>
        <?= $this->Form->create('', ['action' => 'completed/'.$staffInfo['id']]) ?>
          <div class="text-end">
            <button type="submit" class="btn btn-success">Completed</button>
          </div>
        <?= $this->Form->end(); ?>
      <?php endif; ?>
    </div>
  </div>
</section>
