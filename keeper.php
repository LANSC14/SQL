<?php
if (mysqli_connect_errno()) {
    echo "连接至 MySQL 失败: " . mysqli_connect_error();
}

$conn = mysqli_connect('localhost', 'root', '', '112dba11');
mysqli_query($conn, 'SET NAMES utf8');
mysqli_query($conn, 'SET CHARACTER_SET_CLIENT=utf8');
mysqli_query($conn, 'SET CHARACTER_SET_RESULTS=utf8');



// 检查是新增操作还是编辑操作
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        // 处理新增数据
        // 当提交表单时处理新增工作人员数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $position = mysqli_real_escape_string($conn, $_POST["position"]);
    $salary = mysqli_real_escape_string($conn, $_POST["salary"]);
    $department = mysqli_real_escape_string($conn, $_POST["department"]);
    
    // 插入数据的 SQL 语句
    $sqlInsert = "INSERT INTO keeper (name, position, salary, department) VALUES ('$name', '$position', '$salary', '$department')";
    
    if (mysqli_query($conn, $sqlInsert)) {
        // 新增成功时的提示
        echo "<script>alert('恭喜你成功新增！(*ˇωˇ*人)');</script>";
    } else {
        // 新增失败时的提示
        echo "<script>alert('添加失败：" . mysqli_error($conn) . "');</script>";
    }
}

    } elseif ($_POST['action'] == 'edit') {
        // 处理编辑工作人员数据
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editKeeperId'])) {
    $editId = mysqli_real_escape_string($conn, $_POST['editKeeperId']);
    $editName = mysqli_real_escape_string($conn, $_POST['editName']);
    $editPosition = mysqli_real_escape_string($conn, $_POST['editPosition']);
    $editSalary = mysqli_real_escape_string($conn, $_POST['editSalary']);
    $editDepartment = mysqli_real_escape_string($conn, $_POST['editDepartment']);

    // 更新数据的 SQL 语句
    $sqlUpdate = "UPDATE keeper SET name = '$editName', position = '$editPosition', salary = '$editSalary', department = '$editDepartment' WHERE keeperId = '$editId'";

    if (mysqli_query($conn, $sqlUpdate)) {
        echo "<script>alert('修改成功！');</script>";
    } else {
        echo "<script>alert('修改失败：" . mysqli_error($conn) . "');</script>";
    }
}
    }
}



// 处理删除工作人员数据
if (isset($_GET['delete'])) {
    $deleteId = mysqli_real_escape_string($conn, $_GET['delete']);

    // 删除数据的 SQL 语句
    $sqlDelete = "DELETE FROM keeper WHERE keeperId = '$deleteId'";

    if (mysqli_query($conn, $sqlDelete)) {
        echo "<script>alert('删除成功！');</script>";
    } else {
        echo "<script>alert('删除失败：" . mysqli_error($conn) . "');</script>";
    }
}


$keepers = array();
$cardsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$key1 = '';

// 检查是否有搜索请求
if (isset($_GET["keeper_key"]) && trim($_GET["keeper_key"]) != '') {
    $key1 = mysqli_real_escape_string($conn, $_GET["keeper_key"]);
    $start = ($page - 1) * $cardsPerPage;
    $sql = "SELECT keeperId, name, position, salary, department FROM keeper WHERE name LIKE '%$key1%' OR position LIKE '%$key1%' LIMIT $start, $cardsPerPage";
} else {
    $start = ($page - 1) * $cardsPerPage;
    $sql = "SELECT keeperId, name, position, salary, department FROM keeper LIMIT $start, $cardsPerPage";
}

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $keepers[] = $row; // 将每个结果添加到数组中
    }
}

// 获取总记录数以计算总页数
$sqlTotal = "SELECT COUNT(*) FROM keeper";
if (!empty($key1)) {
    $sqlTotal .= " WHERE name LIKE '%$key1%' OR position OR department LIKE '%$key1%'";
}
$totalResult = mysqli_query($conn, $sqlTotal);
$totalRow = mysqli_fetch_array($totalResult);
$totalRecords = $totalRow[0];
$totalPages = ceil($totalRecords / $cardsPerPage);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>工作人員查詢結果</title>
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

    /* 分页链接样式 */
.pagination {
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

.pagination a {
    padding: 8px 16px;
    margin: 4px;
    border: 1px solid #ddd;
    text-decoration: none;
    /* 可以添加其他样式，如背景色、边框圆角等 */
}

.pagination a.active {
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
}

.pagination a:hover:not(.active) {
    background-color: #ddd;
}


/* 模态框（Modal）样式 */
/* 调整按钮样式，使其更小，并在其容器中居中 */
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
            <h1>WLLY ZOO</h1>
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
          <div class="link">
            <a href="keeper.php" class="nav-link">工作人員</a>
          </div>
        </div>
        <img src="image/man2.png" class="background-image" />
      </div>


        <div 
            class="flex column" 
            style="
            align-items: center; 
             justify-content: center; 
             margin-top: 30px;"
      >
        <div style="margin:5px">人員查詢</div>
        <div style="margin-bottom: 10px;">
         <form action="" method="get">
          <input type="text" name="keeper_key" placeholder="輸入名字">
          <input type="submit" >
         </form>
        </div>
      </div>

       
      <button class="add-keeper-btn" onclick="document.getElementById('addKeeperModal').style.display='block'">
        新增工作人員
      </button>

<div id="addKeeperModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addKeeperModal').style.display='none'">&times;</span>
        <form action="" method="post">
        <input type="hidden" name="action" value="add">
        
        <label for="name">姓名:</label>
        <input type="text" id="name" name="name" required>

        <label for="salary">薪水:</label>
        <input type="number" id="salary" name="salary" required step="1000">
        
        <label for="position">職位:</label>
        <select name="position" id="position">
            <!-- 职位的下拉菜单选项 -->
            <option value="總負責人">總負責人</option>
            <option value="高級幹部">高級幹部</option>
            <option value="飼養員">飼養員</option>
            <option value="清潔工">清潔工</option>
            <!-- 更多职位选项 -->
        </select>
        
        <label for="department">部門:</label>
        <select name="department" id="department">
            <!-- 部门的下拉菜单选项 -->
            <option value="臺灣動物區">臺灣動物區</option>
            <option value="兒童動物區">兒童動物區</option>
            <option value="大貓熊館">大貓熊館</option>
            <option value="熱帶雨林區">熱帶雨林區</option>
            <option value="非洲動物區">非洲動物區</option>
            <option value="鳥園">鳥園</option>
            <option value="溫帶動物區">溫帶動物區</option>
            <option value="企鵝館">企鵝館</option>
            <option value="無尾熊館">無尾熊館</option>
            <option value="沙漠動物區">沙漠動物區</option>
            <option value="澳洲動物區">澳洲動物區</option>
            <!-- 更多部门选项 -->
        </select>
          
            <input type="submit" value="提交">
            <button type="reset">重置</button>
    </form>
  </div>
</div>
 
      <div
        style="
          width: 60%; /* 設定分隔線的長度為容器寬度的80% */
          height: 2px; /* 設定分隔線的厚度 */
          background-color: #d3d3d3; /* 淺灰色 */
          margin: 20px auto; /* 上下邊距20px，自動左右邊距實現居中 */
        "
      ></div>

      
        <!-- 表格显示区域 -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>姓名</th>
                <th>薪水</th>
                <th>職位</th>
                <th>負責區域</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($keepers as $keeper): ?>
                <tr>
                    <td><?php echo htmlspecialchars($keeper['keeperId']); ?></td>
                    <td><?php echo htmlspecialchars($keeper['name']); ?></td>
                    <td><?php echo htmlspecialchars($keeper['salary']); ?></td>
                    <td><?php echo htmlspecialchars($keeper['position']); ?></td>
                    <td><?php echo htmlspecialchars($keeper['department']); ?></td>
                    <td>
                      <a href="#" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($keeper)); ?>)" class="edit-delete-link edit-link">编辑</a>
                      <a href="?delete=<?php echo $keeper['keeperId']; ?>" onclick="return confirm('确定删除吗？')" class="edit-delete-link delete-link">删除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <div id="editKeeperModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editKeeperModal').style.display='none'">&times;</span>
        <form action="" method="post">
            <!-- 添加隐藏字段指定操作类型 -->
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="editKeeperId" name="editKeeperId">
            

            <label for="editName">姓名:</label>
            <input type="text" id="editName" name="editName" required>

            <label for="editSalary">薪水:</label>
            <input type="number" id="editSalary" name="editSalary" required step="1000">
            
            <label for="editPosition">職位:</label>
            <select name="editPosition" id="editPosition">
                <!-- 职位的下拉菜单选项，确保与新增模态框一致 -->
                <option value="總負責人">總負責人</option>
                <option value="高級幹部">高級幹部</option>
                <option value="飼養員">飼養員</option>
                <option value="清潔工">清潔工</option>
                <!-- 更多职位选项 -->
            </select>
            
            <label for="editDepartment">部門:</label>
            <select name="editDepartment" id="editDepartment">
                <!-- 部门的下拉菜单选项，确保与新增模态框一致 -->
            <option value="臺灣動物區">臺灣動物區</option>
            <option value="兒童動物區">兒童動物區</option>
            <option value="大貓熊館">大貓熊館</option>
            <option value="熱帶雨林區">熱帶雨林區</option>
            <option value="非洲動物區">非洲動物區</option>
            <option value="鳥園">鳥園</option>
            <option value="溫帶動物區">溫帶動物區</option>
            <option value="企鵝館">企鵝館</option>
            <option value="無尾熊館">無尾熊館</option>
            <option value="沙漠動物區">沙漠動物區</option>
            <option value="澳洲動物區">澳洲動物區</option>
                <!-- 更多部门选项 -->
            </select>
              
            <input type="submit" value="保存修改">
            <button type="reset">重置</button>
        </form>
    </div>
</div>


    <!-- 分页链接 -->
    <div class="pagination">
        <?php if ($page > 1) : ?>
            <a href="?page=<?php echo ($page - 1); ?>">&laquo;</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <a <?php echo ($i === $page) ? 'class="active"' : ''; ?> href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages) : ?>
            <a href="?page=<?php echo ($page + 1); ?>">&raquo;</a>
        <?php endif; ?>
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


        // 當用戶點擊窗口-domain外的地方時，關閉模態框
    window.onclick = function(event) {
        if (event.target == document.getElementById('addKeeperModal')) {
            document.getElementById('addKeeperModal').style.display = "none";
        }
    }


    function openEditModal(keeper) {
        document.getElementById('editKeeperId').value = keeper.keeperId;
        document.getElementById('editName').value = keeper.name;
        document.getElementById('editSalary').value = keeper.salary;
        document.getElementById('editPosition').value = keeper.position;
        document.getElementById('editDepartment').value = keeper.department;
        document.getElementById('editKeeperModal').style.display = 'block';
    }

</script>


  
</body>
</html>