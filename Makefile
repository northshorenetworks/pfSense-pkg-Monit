# $FreeBSD$

PORTNAME=	pfSense-pkg-Monit
PORTVERSION=	5.20.0
CATEGORIES=	sysutils
MASTER_SITES=	# empty
DISTFILES=	# empty
EXTRACT_ONLY=	# empty

MAINTAINER=	james@northshoresoftware.com
COMMENT=	pfSense package Monit

LICENSE=	APACHE20

NO_BUILD=	yes
NO_MTREE=	yes

SUB_FILES=	pkg-install pkg-deinstall
SUB_LIST=	PORTNAME=${PORTNAME}

RUN_DEPENDS=	${LOCALBASE}/bin/monit:sysutils/monit

do-extract:
	${MKDIR} ${WRKSRC}

do-install:
	${MKDIR} ${STAGEDIR}${PREFIX}/pkg
	${MKDIR} ${STAGEDIR}${PREFIX}/pkg/monit
	${MKDIR} ${STAGEDIR}/etc/inc/priv
	${MKDIR} ${STAGEDIR}${PREFIX}/www/packages/monit
	${MKDIR} ${STAGEDIR}${DATADIR}
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/monit/monit_post_install.php \
		${STAGEDIR}${PREFIX}/pkg/monit
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/monit/monit.xml \
		${STAGEDIR}${PREFIX}/pkg/monit
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/monit/monit.inc \
		${STAGEDIR}${PREFIX}/pkg/monit
	${INSTALL_DATA} ${FILESDIR}/etc/inc/priv/monit.priv.inc \
		${STAGEDIR}/etc/inc/priv 
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/www/packages/monit/index.php \
		${STAGEDIR}${PREFIX}/www/packages/monit
	${INSTALL_DATA} ${FILESDIR}${DATADIR}/info.xml \
		${STAGEDIR}${DATADIR}
	@${REINPLACE_CMD} -i '' -e "s|%%PKGVERSION%%|${PKGVERSION}|" \
		${STAGEDIR}${DATADIR}/info.xml

.include <bsd.port.mk>
