# PHP 8.3 on UBI9 (Red Hat's S2I PHP image) + Microsoft SQL Server drivers.
# The stock PHP builder lacks pdo_sqlsrv, hence this Dockerfile — OpenShift's
# "Import from Git" detects it and uses the Docker build strategy automatically.
FROM registry.access.redhat.com/ubi9/php-83:latest

USER 0

# Microsoft ODBC driver 18 + PECL build deps, then the sqlsrv extensions
RUN curl -sSL -o /etc/yum.repos.d/mssql-release.repo https://packages.microsoft.com/config/rhel/9/prod.repo && \
    ACCEPT_EULA=Y dnf install -y msodbcsql18 unixODBC-devel gcc gcc-c++ make php-devel php-pear && \
    pecl channel-update pecl.php.net && \
    pecl install sqlsrv-5.13.2 pdo_sqlsrv-5.13.2 && \
    echo "extension=sqlsrv.so"     > /etc/php.d/30-sqlsrv.ini && \
    echo "extension=pdo_sqlsrv.so" > /etc/php.d/35-pdo_sqlsrv.ini && \
    dnf remove -y gcc gcc-c++ make && dnf clean all

# App source into the S2I app root; fix ownership for arbitrary-UID runtime
COPY app/ /opt/app-root/src/
RUN chown -R 1001:0 /opt/app-root/src && chmod -R g+rw /opt/app-root/src

USER 1001
# No EXPOSE here — the base image already declares 8080; repeating it makes
# `oc new-app` generate duplicate port entries (admission warning 299).
CMD ["/usr/libexec/s2i/run"]
