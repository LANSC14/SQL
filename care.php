<?php
$conn = mysqli_connect('localhost', 'root', '', '112dba11');
if (!$conn) {
    die("连接至 MySQL 失败: " . mysqli_connect_error());
}

mysqli_query($conn, 'SET NAMES utf8');
mysqli_query($conn, 'SET CHARACTER_SET_CLIENT=utf8');
mysqli_query($conn, 'SET CHARACTER_SET_RESULTS=utf8');


// 处理添加逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $keeperId = $_POST['keeperId'];
    $animalIds = $_POST['animalIds'];
    foreach ($animalIds as $animalId) {
        $sqlInsert = "INSERT INTO caresfor (keeperId, animalId) VALUES ('$keeperId', '$animalId')";
        mysqli_query($conn, $sqlInsert) or die('新增失败: ' . mysqli_error($conn));
    }
    echo "<p>新增成功！</p>";
}

// 显示新增表单
function showForm($conn) {
    // 这里仅作为示例，确保实际的字段与您的数据库结构相匹配
    echo "<h2>添加新的照顧關係</h2>";
    echo "<form action='' method='post'>";
    echo "<label for='keeperId'>選擇工作人員:</label>";
    echo "<select name='keeperId'>";
    // 假设有名为keeper的表，其中包含keeperId和name字段
    $keepers = mysqli_query($conn, "SELECT keeperId, name FROM keeper");
    while ($keeper = mysqli_fetch_assoc($keepers)) {
        echo "<option value='".$keeper['keeperId']."'>".$keeper['name']."</option>";
    }
    echo "</select>";
    echo "<label for='animalIds'>選擇動物:</label>";
    echo "<select name='animalIds[]' multiple>";
    // 假设有名为animal的表，其中包含animalId和ch_name字段
    $animals = mysqli_query($conn, "SELECT animalId, ch_name FROM animal");
    while ($animal = mysqli_fetch_assoc($animals)) {
        echo "<option value='".$animal['animalId']."'>".$animal['ch_name']."</option>";
    }
    echo "</select>";
    echo "<input type='submit' name='add' value='新增'>";
    echo "</form>";
}


// 处理更新逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $keeperId = $_POST['keeperId'];
    $animalIds = $_POST['animalIds'];

    // 获取当前keeper已关联的动物ID
    $existingAnimals = [];
    $sqlSelect = "SELECT animalId FROM caresfor WHERE keeperId='$keeperId'";
    $result = mysqli_query($conn, $sqlSelect);
    while ($row = mysqli_fetch_assoc($result)) {
        $existingAnimals[] = $row['animalId'];
    }

    // 删除不再关联的动物
    foreach ($existingAnimals as $existingAnimalId) {
        if (!in_array($existingAnimalId, $animalIds)) {
            $sqlDelete = "DELETE FROM caresfor WHERE keeperId='$keeperId' AND animalId='$existingAnimalId'";
            mysqli_query($conn, $sqlDelete) or die('删除失败: ' . mysqli_error($conn));
        }
    }

    // 添加新关联的动物
    foreach ($animalIds as $animalId) {
        if (!in_array($animalId, $existingAnimals)) {
            $sqlInsert = "INSERT INTO caresfor (keeperId, animalId) VALUES ('$keeperId', '$animalId')";
            mysqli_query($conn, $sqlInsert) or die('新增失败: ' . mysqli_error($conn));
        }
    }

    echo "<p>更新成功！</p>";
}

// 处理删除逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $keeperId = $_POST['keeperId'];
    $animalId = $_POST['animalId'];

    $sqlDelete = "DELETE FROM caresfor WHERE keeperId='$keeperId' AND animalId='$animalId'";
    mysqli_query($conn, $sqlDelete) or die('删除失败: ' . mysqli_error($conn));

    echo "<p>删除成功！</p>";
}


// 显示编辑表单
function showEditForm($conn, $editKeeperId, $editAnimalId) {
    echo "<form action='' method='post' class='form-style'>";
    
    // 固定显示选中的工作人员，不允许更改
    $keeper = mysqli_query($conn, "SELECT * FROM keeper WHERE keeperId='$editKeeperId'");
    $keeperData = mysqli_fetch_assoc($keeper);
    echo "<input type='hidden' name='keeperId' value='".$editKeeperId."'>";
    echo "<label>工作人員: ".$keeperData['name']."</label>";

    echo "<select name='animalIds[]' multiple>";
    $animals = mysqli_query($conn, "SELECT * FROM animal");
    while ($animal = mysqli_fetch_assoc($animals)) {
        $selectedSql = "SELECT * FROM caresfor WHERE keeperId='$editKeeperId' AND animalId='".$animal['animalId']."'";
        $selectedResult = mysqli_query($conn, $selectedSql);
        $selected = mysqli_fetch_assoc($selectedResult) ? "selected" : "";
        echo "<option value='".$animal['animalId']."' $selected>".$animal['ch_name']."</option>";
    }
    echo "</select>";

    echo "<input type='submit' name='update' value='更新' class='btn'>";
    echo "</form>";
}

// 添加查询逻辑
$searchQuery = '';
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['searchQuery'])) {
    $searchQuery = $_GET['searchQuery'];
}


// 显示数据表格
function showTable($conn, $searchQuery) {
    $sql = "SELECT k.name, a.ch_name, c.keeperId, c.animalId FROM keeper k JOIN caresfor c ON k.keeperId = c.keeperId JOIN animal a ON a.animalId = c.animalId";
    if ($searchQuery) {
        $sql .= " WHERE k.name LIKE '%$searchQuery%' OR a.ch_name LIKE '%$searchQuery%'";
    }
    $result = mysqli_query($conn, $sql);
    echo '<table border="1">';
    echo '<tr><th>工作人員</th><th>動物</th><th>操作</th></tr>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>".$row['name']."</td>";
        echo "<td>".$row['ch_name']."</td>";
        echo "<td><a href='?edit=true&keeperId=".$row['keeperId']."&animalId=".$row['animalId']. "' class='edit-btn'>編輯</a> 
              <form method='post' style='display:inline;'>
              <input type='hidden' name='keeperId' value='".$row['keeperId']."'>
              <input type='hidden' name='animalId' value='".$row['animalId']."'>
              <input type='submit' name='delete' value='刪除' class='delete-btn'>
              </form>
              </td>";
        echo "</tr>";
    }
    echo '</table>';
}

// 检测是否处于编辑模式
$editMode = false;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['edit']) && isset($_GET['keeperId'])) {
    $editMode = true;
    $editKeeperId = $_GET['keeperId'];
}

if (isset($_GET['animalId'])) {
    $editAnimalId = $_GET['animalId'];
} else {
    // 处理错误，例如设置默认值或显示错误消息
    $editAnimalId = ''; // 或任何默认值
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>照顧關係 </title>
    <style>
        /* 模態框樣式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        body {
        margin: 0;
        font-family: "Arial", sans-serif;
        background-color: #ffffff;
      }
      .flex {
        display: flex;
      }
      .column {
        flex-direction: column;
      }
      .container {
        position: relative;
        height: 500px;
      }

      .navbar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        display: flex;
        align-items: center;
        background-color: rgba(107, 104, 104, 0.5); /* 暗化效果 */
        padding-left: 80px;
      }

      .nav-link {
        color: #000;
        text-decoration: none;
      }

      .background-image {
        height: 100%;
        width: 100%;
      }
      #scroll-to-top {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 20px;
        cursor: pointer;
      }

      #scroll-to-top:hover {
        background-color: #0056b3;
      }

      #scroll-to-top span {
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      /* 表格样式 */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #ddd;
        text-align: left;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

     .form-style {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    margin-top: 20px;
  }

  select {
    margin: 10px;
  }

  .btn {
    background-color: #4CAF50; /* 绿色 */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 5px;
  }

  .btn:hover {
    background-color: #45a049;
  }

   .form-container {
        text-align: center;
        margin-top: 50px; /* 调整间距 */
    }

    .form-container h2 {
        margin-bottom: 20px; /* 标题和表单之间的间距 */
    }

    .form-container form {
        display: inline-block;
        margin: auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .form-container label {
        margin-top: 10px;
        display: block; /* 确保标签显示在下拉菜单上方 */
    }

    select, .btn {
        display: block; /* 块级显示 */
        margin: 10px auto; /* 上下间距10px，左右自动居中 */
        padding: 10px;
    }

    .btn {
        background-color: #4CAF50; /* 绿色 */
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .btn:hover {
        opacity: 0.8;
    }

    .edit-btn, .delete-btn {
        padding: 5px 10px;
        margin: 0 5px;
        cursor: pointer;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        color: white;
    }

    .edit-btn {
        background-color: #f0ad4e; /* 橙色 */
    }

    .delete-btn {
        background-color: #d9534f; /* 红色 */
    }

    .edit-btn:hover, .delete-btn:hover {
        opacity: 0.8;
    }

    .add-keeper-btn {
    background-color: #4CAF50; /* 绿色按钮 */
    border: none;
    color: white;
    padding: 10px 20px; /* 按钮的内填充更小 */
    text-align: center;
    text-decoration: none;
    display: block; /* 使按钮块级显示 */
    font-size: 14px; /* 字体大小更小 */
    margin: 10px auto; /* 上下边距10px，自动左右边距实现居中 */
    cursor: pointer;
    border-radius: 4px;
}

.modal {
    display: none; /* 預設隱藏 */
    position: fixed;
    z-index: 1; /* 設置在其他元素之上 */
    left: 0;
    top: 0;
    width: 100%; /* 全寬 */
    height: 100%; /* 全高 */
    overflow: auto; /* 啟用滾動條 */
    background-color: rgba(0,0,0,0.4); /* 半透明黑色背景 */
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* 上下邊距10%，自動左右邊距 */
    padding: 20px;
    border: 1px solid #888;
    width: 40%; /* 調整模態框寬度 */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* 樣式化表單 */
.modal-content form {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.modal-content label {
    margin-bottom: 10px;
    margin-top: 10px;
}

.modal-content input[type="text"],
.modal-content input[type="number"],
.modal-content select {
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box; /* 添加以保持一致性 */
}

.modal-content input[type="submit"],
.modal-content button[type="reset"] {
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.modal-content input[type="submit"]:hover {
    background-color: #45a049;
}

.modal-content button[type="reset"]:hover {
    background-color: #f44336;
}

/* 编辑和删除链接的基础样式 */
.edit-delete-link {
    color: #007bff; /* 蓝色 */
    text-decoration: none;
    font-size: 14px;
    border: 1px solid transparent;
    padding: 3px 6px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

/* 鼠标悬停在链接上时的样式 */
.edit-delete-link:hover {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

/* 编辑链接的特定样式 */
.edit-link {
    color: #28a745; /* 绿色 */
}

.edit-link:hover {
    background-color: #28a745;
}

/* 删除链接的特定样式 */
.delete-link {
    color: #dc3545; /* 红色 */
}

.delete-link:hover {
    background-color: #dc3545;
}


      
    </style>
</head>
<body>

    <div>
        <div
        class="flex"
        style="
          background-color: rgba(231, 232, 231, 0.5); /* 半透明背景 */
          backdrop-filter: blur(10px); /* 模糊效果 */
          -webkit-backdrop-filter: blur(10px); /* 針對舊版Safari瀏覽器的支持 */
          padding: 20px;
          height: 80px;
          position: fixed;
          top: 0;
          width: 100%;
          z-index: 100;
        "
      >
        <a href="index.html" class="flex nav-link">
          <img
            src="image/head.png"
            width="70px"
            height="70px"
            style="margin-left: 100px; margin-top: 10px"
          />
          <div style="margin-left: 20px; margin-top: 0px">
            <h1>動物園(Zoo)</h1>
          </div>
        </a>
      </div>

      <div style="height: 120px"></div>

      <div class="container">
        <div class="navbar">
          <div>☰&nbsp;</div>
          <div class="link">
            <a href="index.html" class="nav-link">首頁</a>
          </div>
          <div>&nbsp;☞&nbsp;</div>
          <div>照顧關係</div>
        </div>
        <img src="image/kp.png" class="background-image" />
      </div>




    <!-- 搜索表单 -->
    <div 
            class="flex column" 
            style="
             align-items: center; 
             justify-content: center; 
             margin-top: 30px;
             
             "
      >
      <div style="margin:5px">人員查詢</div>
    <div style="margin-bottom: 10px;"> 
    <form action="" method="get">
        <input type="text" name="searchQuery" placeholder="搜尋..." value="<?php echo $searchQuery; ?>">
        <input type="submit" value="查詢">
    </form>
    </div>
 

    <!-- 触发新增表单的按钮 -->
    <button class="add-keeper-btn" id="addBtn">新增</button>

     </div>

    <!-- 新增模态框 -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <?php showForm($conn); ?>
        </div>
    </div>

    <!-- 编辑模态框 -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="editClose">&times;</span>
            <!-- 这里将显示编辑表单 -->
           <?php if ($editMode) { showEditForm($conn, $editKeeperId, $editAnimalId); } ?>
        </div>
    </div>

    <!-- 显示数据表格 -->
    <div>
        <?php showTable($conn, $searchQuery); ?>
    </div>






    <div style="height: 200px; background-color: #e7e8e7; ">
        <div
          class="flex"
          style="padding: 20px; justify-content: center; margin-top: 80px"
        >
          <div class="flex">
            <div>
              <img src="image/head.png" width="70px" height="70px" />
            </div>
            <div style="margin-left: 5px; margin-top: 20px; margin-left: 10px">
              WLLY ZOO
            </div>
          </div>
          <div class="column" style="margin-left: 300px">
            <div>地址：116台北市文山區新光路二段30號</div>
            <div>電話：0229382300</div>
            <div>更新日期 2023-11-17</div>
          </div>
        </div>
        <div>
          <hr
            style="border-color: black; border-width: 2px; border-style: solid"
          />
          <div class="flex" style="justify-content: center">
            Copyright © 2023 Zoo Inc. All rights reserved.
          </div>
        </div>




    </div> 
    <button id="scroll-to-top" onclick="scrollToTop()">
      <span>TOP</span>
    </button>

    <script>

            window.onclick = function(event) {
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
};
            // 获取模态框元素
        var addModal = document.getElementById("addModal");
        var addBtn = document.getElementById("addBtn");
        var span = document.getElementsByClassName("close")[0];

        // 打开模态框
        addBtn.onclick = function() {
            addModal.style.display = "block";
        }

        // 点击 × 关闭模态框
        span.onclick = function() {
            addModal.style.display = "none";
        }

        // 点击窗口外部关闭模态框
        window.onclick = function(event) {
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
        }

        // 获取编辑模态框元素
var editModal = document.getElementById("editModal");
var editClose = document.getElementById("editClose");

// 如果处于编辑模式，则显示编辑模态框
if (<?php echo $editMode ? 'true' : 'false'; ?>) {
    editModal.style.display = "block";
}

// 点击 × 关闭编辑模态框
editClose.onclick = function() {
    editModal.style.display = "none";
}

// 点击窗口外部关闭编辑模态框
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

// 當用戶滾動頁面時，顯示或隱藏按鈕
      window.onscroll = function () {
        var button = document.getElementById("scroll-to-top");
        if (
          document.body.scrollTop > 20 ||
          document.documentElement.scrollTop > 20
        ) {
          button.style.display = "block";
        } else {
          button.style.display = "none";
        }
      };

      // 當按鈕被點擊時，滾動到頂部
      function scrollToTop() {
        document.body.scrollTop = 0; // 對於一些瀏覽器
        document.documentElement.scrollTop = 0; // 對於現代瀏覽器
      }

</script>
</body>
</html>