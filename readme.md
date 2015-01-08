#G5MenuCreator

##설치
1. 그누보드 관리자 폴더에 adm 카피
2. G5MenuCreator를 상속받은후 메소드 구현

##객채의 생성
~~~~
$g5MenuCreator = new G5MenuBasicCreator( $_REQUEST );
~~~~

##API
* prepareContents : 그누보드 컨텐츠 관리로 생성한 페이지가 나왔을때
* prepareBBS : bo_table로 넘어왔을때
* prepareCate : ca_id로 넘어왔을때
* prepareRequest : 위 내용의 모든 다른 경우