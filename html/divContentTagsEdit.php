<div id="TagAttachmentEdit">
  <form action="contentEdit.php">
    <input type="hidden" name="pageContentID" id="taeContentID" value="<?= $_POST["pageContentID"] ?>"/>
    <input type="hidden" name="userID" id="taeUserID" value="<?= $_SESSION["userID"] ?>"/>
    <?php
    tagCategorySelector($pdo);     // id="tagCatSelect"
    ?>
    <select id="tagSelect">
      <option value="0">Select Tag...</option>
    </select>
    <input type="button" class="btn btn-default btn-xs" value="Attach Tag" onclick="tagAttach()">
    <input type="button" class="btn btn-default btn-xs" value="Remove Tag" onclick="tagRemove()">
  </form>

  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script type="text/javascript">
      $(document).ready(function () {
          $('#tagCatSelect').change(function () {
              var tagCatID = $(this).val();
              fillComboTags(tagCatID);
          });
      })
      ;

      function fillComboTags(tagCatID) {
          $('#tagSelect').empty();
          $('#tagSelect').append("<option>Select Tag...</option>");
          $.ajax({
              type: "POST",
              url: "tagsByTagCategory.php?tagCatID=" + tagCatID,
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              success: function (data) {
                  $('#tagSelect').empty();
                  $('#tagSelect').append("<option value=\'0\'>Select Tag...</option>");
                  $.each(data, function (i, item) {
                      $('#tagSelect').append('<option value="' + data[i].tagID + '">' + data[i].tag + '</option>');
                  });
              },
              complete: function () {
              }
          });
      }

      function tagAttach() {
          $.ajax({
              type: "POST",
              data: {action: 'attach',theTagged: $("#taeContentID").val(),theTag: $("#tagSelect").val(),theUser: $("#taeUserID").val()},
              // data: { action: taeAction, theTagged: taeContentID, theTag: taeTagID, theUser: taeUserID }
              // data: {"action":"' + taeAction + '", "theTagged":"' + taeContentID + ', "theTag":"' + taeTagID + ', "theUser":"' + taeUserID + '"},
              // data: {action: taeAction, theTagged: taeContentID, theTag: taeTagID, theUser: taeUserID}
              url: "fnTagAttachmentEdit.php",
              success: function () {
                  window.location.reload(true);
              }
          });
      }

      function tagRemove() {
          $.ajax({
              type: "POST",
              data: {action: 'remove',theTagged: $("#taeContentID").val(),theTag: $("#tagSelect").val(),theUser: $("#taeUserID").val()},
               url: "fnTagAttachmentEdit.php",
              success: function () {
                  window.location.reload(true);
              }
          });
      }
  </script>
</div>
