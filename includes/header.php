<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php 
                                    $user = getCurrentUser();
                                    echo $user['full_name'] ?? 'User';
                                ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isLoggedIn()): ?>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                        </li>

                        <?php if (hasRole('admin')): ?>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'programs.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/programs.php"><i class="fas fa-book"></i> Programs</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/courses.php"><i class="fas fa-chalkboard"></i> Courses</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/users.php"><i class="fas fa-users"></i> Users</a></li>
                        <?php elseif (hasRole('faculty')): ?>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faculty/courses.php"><i class="fas fa-chalkboard"></i> My Courses</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'assessments.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faculty/assessments.php"><i class="fas fa-clipboard-check"></i> Assessments</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'question-papers.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faculty/question-papers.php"><i class="fas fa-file-alt"></i> Question Papers</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'course-files.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faculty/course-files.php"><i class="fas fa-folder"></i> Course Files</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'attainment.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faculty/attainment.php"><i class="fas fa-chart-bar"></i> Attainment</a></li>
                        <?php elseif (hasRole('student')): ?>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/student/courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'marks.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/student/marks.php"><i class="fas fa-star"></i> My Marks</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'progress.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/student/progress.php"><i class="fas fa-chart-pie"></i> Progress</a></li>
                        <?php endif; ?>

                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="<?php echo SITE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-3">
    <?php endif; ?>
