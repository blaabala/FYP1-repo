<?php include("header.php"); ?>
    <div class="container">
        <div class="box form-box">
            <header class="roboto-black-italic">Edit User Profile</header>
            <form action="" method="post">
                <div class="field">
                    <label for="username">New User Name</label>
                    <input required type="text" id="username" name="username" placeholder="Enter your full name">
                </div>
                <div class="field">
                    <label for="usermail">New User Email</label>
                    <input required type="text" id="usermail" name="usermail" placeholder="Enter your email address">
                </div>
                <div class="field">
                    <label for="password">New Password</label>
                    <input required type="text" id="password" name="password" placeholder="Enter your password">
                </div>
                <div class="field">
                    <label for="password">New User Role</label>
                    <select id="userrole" name="userrole">
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                      </select>
                </div>
                <div class="field">
                    <label for="password">Faculty</label>
                    <select id="faculty" name="faculty">
                        <option value="MK-FMHS">M. Kandiah Faculty of Medicine and Health Sciences</option>
                        <option value="LKC-FES">Lee Kong Chian Faculty of Engineering and Science</option>
                        <option value="FEGT">Faculty of Engineering and Green Technology</option>
                        <option value="FICT">Faculty of Information and Communication Technology</option>
                        <option value="FSc">Faculty of Science</option>
                        <option value="FAM">Faculty of Accountancy and Management (Sungai Long Campus)</option>
                        <option value="FBF">Faculty of Business and Finance (Kampar Campus)</option>
                        <option value="FAS">Faculty of Arts and Social Science (Kampar Campus)</option>
                        <option value="FCI">Faculty of Creative Industries</option>
                        <option value="Postgraduate">Institute of Postgraduate Studies & Research</option>
                        <option value="ICS">Institute of Chinese Studies</option>
                        <option value="IMLD">Institute of Management and Leadership Development</option>
                        <option value="CFS-KPR">Centre for Foundation Studies (Kampar Campus)</option>
                        <option value="CFS-SGLONG">Centre for Foundation Studies (Sungai Long Campus)</option>
                        <option value="CEE">Centre for Extension Education</option>
                        <option value="CCCD">Centre for Corporate and Community Development</option>
                      </select>
                </div>
                <div class="field">
                    <label for="phoneno">New Contact Number</label>
                    <input required type="text" id="phoneno" name="phoneno" placeholder="i.e.: +60123456789">
                </div>
                <div class="field">
                    <input required type="submit" name="submit" value="Submit" class="btn">
                </div>
            </form>
        </div>
    </div>
<?php include("footer.php"); ?>