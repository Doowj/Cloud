<?php
require '../components/_base.php'; // Include base file for DB connection and other utilities
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authorization check for 'Admin'
auth('Admin');
$_title="Admin - View Members";
// Handle search query
$search = req('search', '');

// Fetch members based on search query
if ($search) {
    $stm = $_db->prepare('SELECT * FROM user WHERE role = ? AND (name LIKE ?)');
    $stm->execute(['Member', "%$search%"]);
} else {
    // Fetch all members when no search is performed
    $stm = $_db->prepare('SELECT * FROM user WHERE role = ?');
    $stm->execute(['Member']);
}
$members = $stm->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/admin_member_table.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?> <!-- Include Admin Header -->

<section>
   <h1>View Members</h1>

   <!-- Search bar for searching members by name or email -->
   <div class="actions-container">
   <form method="GET" action="">
      <input type="search" name="search" placeholder="Search by name" value="<?= encode($search) ?>">
      <button type="submit">Search</button>
   </form>
   <!-- Add Member button -->
   <button class="add-member-btn" onclick="window.location.href='admin_member_add.php'" style="background-color: green; color: white; padding: 8px 16px; border: none; cursor: pointer;">Add Member</button>
   </div>
   <!-- Table to display all members -->
   <table>
      <thead>
         <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Email</th>
            <th>Telephone</th>
            <th>Address</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php if ($members): ?>
            <?php foreach ($members as $member): ?>
               <tr>
                  <td><?= encode($member->id) ?></td>
                  <td> <img class="member-img" src="/user_img/<?= encode($member->image) ?>"></td>
                  <td><?= encode($member->name) ?></td>
                  <td><?= encode($member->gender) ?></td>
                  <td><?= encode($member->dob) ?></td>
                  <td><?= encode($member->email) ?></td>
                  <td><?= encode($member->telephone) ?></td>
                  <td><?= encode($member->address) ?></td>
                  <td>
                  <button onclick="window.location.href='admin_member_edit.php?id=<?= encode($member->id) ?>'">Edit</button> 
                  <form action="admin_member_delete.php" method="POST" style="display:inline;">
          
                     <?= html_hidden2("id","",encode($member->id))?>
                     <button type="submit" style="background-color: red; color: white; padding: 8px 16px; border: none; cursor: pointer;" 
                              onclick="return confirm('Are you sure you want to delete this member?')">
                           Delete
                     </button>
                  </form>
                  <form action="admin_member_toggle_status.php" method="POST" style="display:inline;">
                  <?= html_hidden2("id","",encode($member->id))?>
                  <?php
                     $items = [
                        'active' => 'Active',
                        'blocked' => 'Blocked',
                    ];
                    
                    echo html_select2('status', $items, null, 'onchange="confirmStatusChange(this)"', $member->status);
                    
                  ?>
                  </form>
                  </td>
               </tr>
            <?php endforeach; ?>
         <?php else: ?>
            <tr>
               <td colspan="4">No members found.</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>
   </section>

   <script>
      function confirmStatusChange(selectElement) {
         const form = selectElement.closest('form'); // Get the form
         const status = selectElement.value;
         const action = status === 'active' ? 'activate' : 'block';
         
         if (confirm(`Are you sure you want to ${action} this member?`)) {
            form.submit(); // Submit the form if confirmed
         } else {
            // Reset the selection to the previous value if canceled
            selectElement.value = selectElement.options[0].value; // Change this to the previous status
         }
      }
      </script>



   <script src="../js/admin_script.js"></script>
</body>
</html>
