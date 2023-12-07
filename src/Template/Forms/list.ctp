
<section class="py-5 text-center container">
  <div class="row py-lg-5">
    <div class="col-lg-10 col-md-12 mx-auto">
        <div class="table-responsive">
            <table class='table'>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Client Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= $user['username'] ?></td>
                            <td><?= ucwords($user['first_name'].' '.$user['last_name']) ?></td>
                            <td><?= $user['client_name'] ?></td>
                            <td><?= ucwords($user['status']) ?></td>
                            <td><?= $this->Html->link(
                                'View',
                                '/forms/index/'.$user['id'],
                                ['class' => 'btn btn-primary',]
                            ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</section>
