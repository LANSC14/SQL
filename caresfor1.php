<?php
$conn = mysqli_connect('localhost', 'root', '', '112dba11');
if (!$conn) {
    die("连接至 MySQL 失败: " . mysqli_connect_error());
}

mysqli_query($conn, 'SET NAMES utf8');
mysqli_query($conn, 'SET CHARACTER_SET_CLIENT=utf8');
mysqli_query($conn, 'SET CHARACTER_SET_RESULTS=utf8');

// 处理新增和删除表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $keeperId = $_POST['keeperId'];
        $animalIds = $_POST['animalIds'];
        foreach ($animalIds as $animalId) {
            $sql = "INSERT INTO caresfor (keeperId, animalId) VALUES ('$keeperId', '$animalId')";
            mysqli_query($conn, $sql) or die('新增失败: ' . mysqli_error($conn));
        }
    } elseif (isset($_POST['delete'])) {
        $keeperId = $_POST['keeperId'];
        $animalId = $_POST['animalId'];
        $sql = "DELETE FROM caresfor WHERE keeperId='$keeperId' AND animalId='$animalId'";
        mysqli_query($conn, $sql) or die('删除失败: ' . mysqli_error($conn));
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// 显示新增表单
function showForm($conn) {
    echo "<div class='form-container'>";
    echo "<h2>添加新的照顧關係</h2>"; // 添加描述性文字
    echo "<form action='' method='post'>";
    echo "<label for='keeperId'>選擇工作人員:</label>"; // 添加描述性标签
    echo "<select name='keeperId'>";
    $keepers = mysqli_query($conn, "SELECT * FROM keeper");
    while ($keeper = mysqli_fetch_assoc($keepers)) {
        echo "<option value='".$keeper['keeperId']."'>".$keeper['name']."</option>";
    }
    echo "</select>";
    echo "<label for='animalIds[]'>選擇動物:</label>"; // 添加描述性标签
    echo "<select name='animalIds[]' multiple>";
    $animals = mysqli_query($conn, "SELECT * FROM animal");
    while ($animal = mysqli_fetch_assoc($animals)) {
        echo "<option value='".$animal['animalId']."'>".$animal['ch_name']."</option>";
    }
    echo "</select>";
    echo "<input type='submit' name='add' value='新增' class='btn'>";
    echo "</form>";
    echo "</div>";
}

// 显示数据表格
function showTable($conn, $searchTerm) {
    $sql = "SELECT k.name as keeperName, a.ch_name as animalName, c.keeperId, c.animalId FROM keeper k JOIN caresfor c ON k.keeperId = c.keeperId JOIN animal a ON a.animalId = c.animalId";
    if ($searchTerm) {
        $sql .= " WHERE k.name LIKE '%$searchTerm%' OR a.ch_name LIKE '%$searchTerm%'";
    }
    $result = mysqli_query($conn, $sql);
    echo '<table border="1">';
    echo '<tr><th>工作人員</th><th>動物</th><th>操作</th></tr>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>".$row['keeperName']."</td>";  // 使用别名'keeperName'
        echo "<td>".$row['animalName']."</td>";  // 使用别名'animalName'
        echo "<td><a href='?edit=true&keeperId=".$row['keeperId']."&animalId=".$row['animalId']. "' class='edit-btn'>編輯</a> 
              <form method='post' style='display:inline;'>
              <input type='hidden' name='keeperId' value='".$row['keeperId']."'>
              <input type='hidden' name='animalId' value='".$row['animalId']."'>
              <input type='submit' name='delete' value='删除' class='delete-btn'>
              </form>
              </td>";
        echo "</tr>";
    }
    echo '</table>';
}


// 处理更新逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $keeperId = $_POST['keeperId'];
    $animalIds = $_POST['animalIds'];

    // 首先删除现有关系
    $sqlDelete = "DELETE FROM caresfor WHERE keeperId='$keeperId'";
    mysqli_query($conn, $sqlDelete) or die('删除失败: ' . mysqli_error($conn));

    // 然后添加新关系
    foreach ($animalIds as $animalId) {
        $sqlInsert = "INSERT INTO caresfor (keeperId, animalId) VALUES ('$keeperId', '$animalId')";
        mysqli_query($conn, $sqlInsert) or die('新增失败: ' . mysqli_error($conn));
    }

    // 显示更新成功的消息
    echo "<p>更新成功！</p>";
}

// 显示编辑表单
function showEditForm($conn, $editKeeperId, $editAnimalId) {
    echo "<form action='' method='post' class='form-style'>";
    
    // 固定显示选中的工作人员，不允许更改
    $keeper = mysqli_query($conn, "SELECT * FROM keeper WHERE keeperId='$editKeeperId'");
    $keeperData = mysqli_fetch_assoc($keeper);
    echo "<input type='hidden' name='keeperId' value='".$editKeeperId."'>";
    echo "<label>工作人员: ".$keeperData['name']."</label>";

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

// 检查是否处于编辑模式
if (isset($_GET['edit']) && $_GET['edit'] == 'true') {
    $editKeeperId = $_GET['keeperId'];
    $editAnimalId = $_GET['animalId'];
    $editMode = true;
} else {
    $editMode = false;
}

$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>照顧關係</title>
    <style>
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
    opacity: 0.8;
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

     
     <div class="search-container">
      <div 
            class="flex column" 
            style="
             align-items: center; 
             justify-content: center; 
             margin-top: 30px;
             
             "
      >
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
        <input type="text" name="search" placeholder="搜索動物或工作人員...">
        <button type="submit">搜索</button>
    </form>
</div>
</div>


       <div>
            <?php 
            if ($editMode) {
                showEditForm($conn, $editKeeperId, $editAnimalId);
            } else {
                showForm($conn);
            }
            ?>
        </div>

        <div>
            <?php showTable($conn, $searchTerm); ?>
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